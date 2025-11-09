<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\AnimalOfflineChange;
use App\Models\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AnimalOfflineChangeController extends Controller
{
    /**
     * Create a new offline change record
     * POST /api/animal-offline-changes
     */
    public function store(Request $request)
    {
        // Normalize animal_ids - handle JSON string, array, or comma-separated
        $animal_ids = $request->input('animal_ids');
        if (is_string($animal_ids)) {
            // Try to decode as JSON first
            $decoded = json_decode($animal_ids, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $animal_ids = $decoded;
            } else {
                // Try comma-separated values
                $animal_ids = array_map('trim', explode(',', $animal_ids));
                $animal_ids = array_filter($animal_ids, function($val) {
                    return is_numeric($val);
                });
                $animal_ids = array_map('intval', $animal_ids);
            }
            $request->merge(['animal_ids' => $animal_ids]);
        }

        // Normalize change_data - handle JSON string or array
        $change_data = $request->input('change_data');
        if (is_string($change_data)) {
            $decoded = json_decode($change_data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $change_data = $decoded;
            }
            $request->merge(['change_data' => $change_data]);
        }

        $validator = Validator::make($request->all(), [
            'local_id' => 'required|string',
            'animal_ids' => 'required|array',
            'animal_ids.*' => 'integer',
            'change_type' => 'required|string',
            'change_data' => 'required|array',
            'timestamp' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return Utils::response([
                'code' => 0,
                'message' => 'Validation failed: ' . json_encode($validator->errors()),
                'data' => null
            ]);
        }

        $user_id = Utils::get_user_id($request);
        if ($user_id < 1) {
            return Utils::response([
                'code' => 0,
                'message' => 'User not found',
                'data' => null
            ]);
        }

        // Check if record with same local_id already exists
        $existing = AnimalOfflineChange::where('local_id', $request->local_id)->first();
        if ($existing) {
            return Utils::response([
                'code' => 1,
                'message' => 'Change already submitted',
                'data' => $existing
            ]);
        }

        try {
            $change = new AnimalOfflineChange();
            $change->local_id = $request->local_id;
            $change->setAnimalIdsArray($request->animal_ids);
            $change->change_type = $request->change_type;
            $change->setChangeDataArray($request->change_data);
            $change->changed_by_user_id = $user_id;
            $change->timestamp = $request->timestamp;
            $change->status = 'pending';
            $change->save();

            // Process all pending changes for this user (including the new one)
            $this->processAllPendingChanges($user_id);

            // Reload the change to get updated status
            $change->refresh();

            return Utils::response([
                'code' => 1,
                'message' => 'Change submitted and processed successfully',
                'data' => $change
            ]);
        } catch (\Exception $e) {
            return Utils::response([
                'code' => 0,
                'message' => 'Failed to save change: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }

    /**
     * Get user's offline changes
     * GET /api/animal-offline-changes
     */
    public function index(Request $request)
    {
        $user_id = Utils::get_user_id($request);
        if ($user_id < 1) {
            return Utils::response([
                'code' => 0,
                'message' => 'User not found',
                'data' => []
            ]);
        }

        $status = $request->get('status'); // Filter by status if provided
        $limit = $request->get('limit', 100);

        $query = AnimalOfflineChange::where('changed_by_user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($status) {
            $query->where('status', $status);
        }

        $changes = $query->get();

        return Utils::response([
            'code' => 1,
            'message' => 'Success. Count: ' . count($changes),
            'data' => $changes
        ]);
    }

    /**
     * Process offline changes and apply them to animals
     * POST /api/animal-offline-changes/process/{id}
     */
    public function process(Request $request, $id)
    {
        $user_id = Utils::get_user_id($request);
        if ($user_id < 1) {
            return Utils::response([
                'code' => 0,
                'message' => 'User not found',
                'data' => null
            ]);
        }

        $change = AnimalOfflineChange::find($id);
        if (!$change) {
            return Utils::response([
                'code' => 0,
                'message' => 'Change record not found',
                'data' => null
            ]);
        }

        // Only process if pending or failed
        if (!in_array($change->status, ['pending', 'failed'])) {
            return Utils::response([
                'code' => 0,
                'message' => 'Change already processed',
                'data' => $change
            ]);
        }

        // Mark as processing
        $change->status = 'processing';
        $change->processing_by_user_id = $user_id;
        $change->save();

        try {
            DB::beginTransaction();

            $animalIds = $change->getAnimalIdsArray();
            $changeData = $change->getChangeDataArray();
            $changeType = $change->change_type;

            // Process based on change type
            $result = $this->applyChange($animalIds, $changeType, $changeData, $user_id);

            if ($result['success']) {
                $change->status = 'synced';
                $change->processed_at = time();
                $change->error_message = null;
                $change->save();

                DB::commit();

                return Utils::response([
                    'code' => 1,
                    'message' => $result['message'],
                    'data' => $change
                ]);
            } else {
                throw new \Exception($result['message']);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            $change->status = 'failed';
            $change->error_message = $e->getMessage();
            $change->save();

            return Utils::response([
                'code' => 0,
                'message' => 'Failed to process change: ' . $e->getMessage(),
                'data' => $change
            ]);
        }
    }

    /**
     * Apply the change to animals
     */
    private function applyChange($animalIds, $changeType, $changeData, $userId)
    {
        if (empty($animalIds)) {
            return ['success' => false, 'message' => 'No animals specified'];
        }

        // Verify animals belong to user
        $animals = Animal::whereIn('id', $animalIds)
            ->where('administrator_id', $userId)
            ->get();

        if ($animals->count() != count($animalIds)) {
            return ['success' => false, 'message' => 'Some animals not found or do not belong to user'];
        }

        $updatedCount = 0;

        switch ($changeType) {
            case 'change_farm':
                if (!isset($changeData['farm_id'])) {
                    return ['success' => false, 'message' => 'Farm ID not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['farm_id' => $changeData['farm_id']]);
                $updatedCount = count($animalIds);
                break;

            case 'change_group':
                if (!isset($changeData['group_id'])) {
                    return ['success' => false, 'message' => 'Group ID not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['group_id' => $changeData['group_id']]);
                $updatedCount = count($animalIds);
                break;

            case 'change_status':
                if (!isset($changeData['status'])) {
                    return ['success' => false, 'message' => 'Status not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['status' => $changeData['status']]);
                $updatedCount = count($animalIds);
                break;

            case 'change_e_id':
                // ID tags need to be updated individually to ensure uniqueness
                if (!isset($changeData['e_id_map'])) {
                    return ['success' => false, 'message' => 'E-ID mapping not provided'];
                }
                foreach ($animals as $animal) {
                    if (isset($changeData['e_id_map'][$animal->id])) {
                        $newEId = $changeData['e_id_map'][$animal->id];
                        // Check if e_id already exists
                        $existing = Animal::where('e_id', $newEId)
                            ->where('id', '!=', $animal->id)
                            ->first();
                        if ($existing) {
                            return ['success' => false, 'message' => "E-ID $newEId already exists"];
                        }
                        $animal->e_id = $newEId;
                        $animal->save();
                        $updatedCount++;
                    }
                }
                break;

            case 'change_v_id':
                // Visual ID tags
                if (!isset($changeData['v_id_map'])) {
                    return ['success' => false, 'message' => 'V-ID mapping not provided'];
                }
                foreach ($animals as $animal) {
                    if (isset($changeData['v_id_map'][$animal->id])) {
                        $animal->v_id = $changeData['v_id_map'][$animal->id];
                        $animal->save();
                        $updatedCount++;
                    }
                }
                break;

            case 'change_breed':
                if (!isset($changeData['breed'])) {
                    return ['success' => false, 'message' => 'Breed not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['breed' => $changeData['breed']]);
                $updatedCount = count($animalIds);
                break;

            case 'change_sex':
                if (!isset($changeData['sex'])) {
                    return ['success' => false, 'message' => 'Sex not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['sex' => $changeData['sex']]);
                $updatedCount = count($animalIds);
                break;

            case 'change_type':
                if (!isset($changeData['type'])) {
                    return ['success' => false, 'message' => 'Type not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['type' => $changeData['type']]);
                $updatedCount = count($animalIds);
                break;

            case 'change_dob':
                if (!isset($changeData['dob'])) {
                    return ['success' => false, 'message' => 'Date of birth not provided'];
                }
                // Validate date format (YYYY-MM-DD)
                $dob = $changeData['dob'];
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                    return ['success' => false, 'message' => 'Invalid date format. Expected YYYY-MM-DD'];
                }
                // Validate date is not in the future
                if (strtotime($dob) > time()) {
                    return ['success' => false, 'message' => 'Date of birth cannot be in the future'];
                }
                Animal::whereIn('id', $animalIds)->update(['dob' => $dob]);
                $updatedCount = count($animalIds);
                break;

            case 'change_worth':
                if (!isset($changeData['current_worth'])) {
                    return ['success' => false, 'message' => 'Current worth not provided'];
                }
                $worth = $changeData['current_worth'];
                // Validate it's a positive number
                if (!is_numeric($worth) || $worth < 0) {
                    return ['success' => false, 'message' => 'Current worth must be a positive number'];
                }
                Animal::whereIn('id', $animalIds)->update(['current_worth' => $worth]);
                $updatedCount = count($animalIds);
                break;

            case 'change_conception':
                if (!isset($changeData['conception_method'])) {
                    return ['success' => false, 'message' => 'Conception method not provided'];
                }
                $method = $changeData['conception_method'];
                // Validate conception method is one of the allowed values
                $validMethods = ['Natural', 'AI', 'ET'];
                if (!in_array($method, $validMethods)) {
                    return ['success' => false, 'message' => 'Invalid conception method. Must be Natural, AI, or ET'];
                }
                Animal::whereIn('id', $animalIds)->update(['conception_method' => $method]);
                $updatedCount = count($animalIds);
                break;

            case 'change_sire':
                if (!isset($changeData['sire_id'])) {
                    return ['success' => false, 'message' => 'Sire ID not provided'];
                }
                $sireId = $changeData['sire_id'];
                // Validate sire exists and is male
                $sire = Animal::find($sireId);
                if (!$sire) {
                    return ['success' => false, 'message' => 'Sire animal not found'];
                }
                if ($sire->sex != 'Male') {
                    return ['success' => false, 'message' => 'Sire must be a male animal'];
                }
                // Update both sire_id and parent_id fields
                Animal::whereIn('id', $animalIds)->update([
                    'sire_id' => $sireId,
                    'parent_id' => $sireId
                ]);
                $updatedCount = count($animalIds);
                break;

            default:
                return ['success' => false, 'message' => 'Unknown change type: ' . $changeType];
        }

        return [
            'success' => true,
            'message' => "Successfully updated $updatedCount animal(s)"
        ];
    }

    /**
     * Process all pending changes for a user
     * This ensures changes are applied immediately and individually
     */
    private function processAllPendingChanges($userId)
    {
        // Get all pending and failed changes for this user
        $pendingChanges = AnimalOfflineChange::where('changed_by_user_id', $userId)
            ->whereIn('status', ['pending', 'failed'])
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($pendingChanges as $change) {
            try {
                // Process this change individually
                $this->processSingleChange($change, $userId);
            } catch (\Exception $e) {
                // Log error but continue with other changes
                \Log::error("Failed to process change {$change->id}: " . $e->getMessage());
                
                // Mark this specific change as failed
                $change->status = 'failed';
                $change->error_message = $e->getMessage();
                $change->save();
            }
        }
    }

    /**
     * Process a single change with individual animal error handling
     */
    private function processSingleChange($change, $userId)
    {
        // Mark as processing
        $change->status = 'processing';
        $change->processing_by_user_id = $userId;
        $change->save();

        try {
            DB::beginTransaction();

            $animalIds = $change->getAnimalIdsArray();
            $changeData = $change->getChangeDataArray();
            $changeType = $change->change_type;

            // Process with graceful error handling for individual animals
            $result = $this->applyChangeGracefully($animalIds, $changeType, $changeData, $userId);

            if ($result['success']) {
                $change->status = 'synced';
                $change->error_message = null;
                $change->processed_at = time();
                
                if (isset($result['partial']) && $result['partial']) {
                    // Some animals failed but some succeeded
                    $change->error_message = $result['message'];
                }
            } else {
                $change->status = 'failed';
                $change->error_message = $result['message'];
            }

            $change->save();
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            
            $change->status = 'failed';
            $change->error_message = 'Processing error: ' . $e->getMessage();
            $change->save();
            
            throw $e;
        }
    }

    /**
     * Apply change with individual animal error handling
     * One animal's failure should not stop others from being updated
     */
    private function applyChangeGracefully($animalIds, $changeType, $changeData, $userId)
    {
        if (empty($animalIds)) {
            return ['success' => false, 'message' => 'No animals specified'];
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        // For bulk operations (non-unique fields), try bulk update first
        if (in_array($changeType, ['change_farm', 'change_group', 'change_status', 'change_breed', 
                                    'change_sex', 'change_dob', 'change_worth', 'change_conception', 'change_sire', 'change_mother'])) {
            try {
                // Verify animals belong to user
                $validAnimalIds = Animal::whereIn('id', $animalIds)
                    ->where('administrator_id', $userId)
                    ->pluck('id')
                    ->toArray();

                if (count($validAnimalIds) != count($animalIds)) {
                    $invalidCount = count($animalIds) - count($validAnimalIds);
                    $errors[] = "$invalidCount animal(s) not found or do not belong to user";
                    $failCount += $invalidCount;
                }

                if (!empty($validAnimalIds)) {
                    $updateResult = $this->performBulkUpdate($validAnimalIds, $changeType, $changeData);
                    if ($updateResult['success']) {
                        $successCount += count($validAnimalIds);
                    } else {
                        return $updateResult; // Return error for validation issues
                    }
                }
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
            }
        } else {
            // For operations requiring individual handling (e.g., E-ID, V-ID with uniqueness)
            foreach ($animalIds as $animalId) {
                try {
                    $animal = Animal::where('id', $animalId)
                        ->where('administrator_id', $userId)
                        ->first();

                    if (!$animal) {
                        $errors[] = "Animal $animalId not found";
                        $failCount++;
                        continue;
                    }

                    $individualResult = $this->updateIndividualAnimal($animal, $changeType, $changeData);
                    if ($individualResult['success']) {
                        $successCount++;
                    } else {
                        $errors[] = "Animal {$animal->id}: " . $individualResult['message'];
                        $failCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Animal $animalId: " . $e->getMessage();
                    $failCount++;
                }
            }
        }

        // Determine overall success
        if ($successCount > 0 && $failCount == 0) {
            return [
                'success' => true,
                'message' => "Successfully updated $successCount animal(s)"
            ];
        } elseif ($successCount > 0 && $failCount > 0) {
            return [
                'success' => true,
                'partial' => true,
                'message' => "Updated $successCount animal(s), $failCount failed: " . implode('; ', $errors)
            ];
        } else {
            return [
                'success' => false,
                'message' => "Failed to update animals: " . implode('; ', $errors)
            ];
        }
    }

    /**
     * Perform bulk update for compatible change types
     */
    private function performBulkUpdate($animalIds, $changeType, $changeData)
    {
        switch ($changeType) {
            case 'change_farm':
                if (!isset($changeData['farm_id'])) {
                    return ['success' => false, 'message' => 'Farm ID not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['farm_id' => $changeData['farm_id']]);
                break;

            case 'change_group':
                if (!isset($changeData['group_id'])) {
                    return ['success' => false, 'message' => 'Group ID not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['group_id' => $changeData['group_id']]);
                break;

            case 'change_status':
                if (!isset($changeData['status'])) {
                    return ['success' => false, 'message' => 'Status not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['status' => $changeData['status']]);
                break;

            case 'change_breed':
                if (!isset($changeData['breed'])) {
                    return ['success' => false, 'message' => 'Breed not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['breed' => $changeData['breed']]);
                break;

            case 'change_sex':
                if (!isset($changeData['sex'])) {
                    return ['success' => false, 'message' => 'Sex not provided'];
                }
                Animal::whereIn('id', $animalIds)->update(['sex' => $changeData['sex']]);
                break;

            case 'change_dob':
                if (!isset($changeData['dob'])) {
                    return ['success' => false, 'message' => 'Date of birth not provided'];
                }
                // Validate date format
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $changeData['dob'])) {
                    return ['success' => false, 'message' => 'Invalid date format. Expected YYYY-MM-DD'];
                }
                // Check if date is not in future
                if (strtotime($changeData['dob']) > time()) {
                    return ['success' => false, 'message' => 'Date of birth cannot be in the future'];
                }
                Animal::whereIn('id', $animalIds)->update(['dob' => $changeData['dob']]);
                break;

            case 'change_worth':
                if (!isset($changeData['current_worth'])) {
                    return ['success' => false, 'message' => 'Current worth not provided'];
                }
                // Validate worth is numeric and positive
                if (!is_numeric($changeData['current_worth']) || $changeData['current_worth'] < 0) {
                    return ['success' => false, 'message' => 'Current worth must be a positive number'];
                }
                Animal::whereIn('id', $animalIds)->update(['current_worth' => $changeData['current_worth']]);
                break;

            case 'change_conception':
                if (!isset($changeData['conception_method'])) {
                    return ['success' => false, 'message' => 'Conception method not provided'];
                }
                // Validate conception method
                $validMethods = ['Natural', 'AI', 'ET'];
                if (!in_array($changeData['conception_method'], $validMethods)) {
                    return ['success' => false, 'message' => 'Invalid conception method. Must be Natural, AI, or ET'];
                }
                Animal::whereIn('id', $animalIds)->update(['conception_method' => $changeData['conception_method']]);
                break;

            case 'change_sire':
                if (!isset($changeData['sire_id'])) {
                    return ['success' => false, 'message' => 'Sire ID not provided'];
                }
                $sireId = $changeData['sire_id'];
                // Validate sire exists and is male
                $sire = Animal::find($sireId);
                if (!$sire) {
                    return ['success' => false, 'message' => 'Sire animal not found'];
                }
                if ($sire->sex != 'Male') {
                    return ['success' => false, 'message' => 'Sire must be a male animal'];
                }
                Animal::whereIn('id', $animalIds)->update([
                    'sire_id' => $sireId,
                    'parent_id' => $sireId
                ]);
                break;

            case 'change_mother':
                if (!isset($changeData['mother_id'])) {
                    return ['success' => false, 'message' => 'Mother ID not provided'];
                }
                $motherId = $changeData['mother_id'];
                
                // Allow null/empty to disconnect mother
                if (empty($motherId) || $motherId === 'null' || $motherId === '0') {
                    Animal::whereIn('id', $animalIds)->update([
                        'parent_id' => null,
                        'has_parent' => 'No'
                    ]);
                } else {
                    // Validate mother exists and is female
                    $mother = Animal::find($motherId);
                    if (!$mother) {
                        return ['success' => false, 'message' => 'Mother animal not found'];
                    }
                    if ($mother->sex != 'Female') {
                        return ['success' => false, 'message' => 'Mother must be a female animal'];
                    }
                    
                    // Prevent animal from being its own parent
                    if (in_array($motherId, $animalIds)) {
                        return ['success' => false, 'message' => 'An animal cannot be its own mother'];
                    }
                    
                    Animal::whereIn('id', $animalIds)->update([
                        'parent_id' => $motherId,
                        'has_parent' => 'Yes'
                    ]);
                }
                break;

            default:
                return ['success' => false, 'message' => 'Unknown change type: ' . $changeType];
        }

        return ['success' => true];
    }

    /**
     * Update individual animal (for E-ID, V-ID, etc.)
     */
    private function updateIndividualAnimal($animal, $changeType, $changeData)
    {
        switch ($changeType) {
            case 'change_e_id':
                if (!isset($changeData['e_id'])) {
                    return ['success' => false, 'message' => 'E-ID not provided'];
                }
                $newEId = $changeData['e_id'];
                // Check uniqueness
                $existing = Animal::where('e_id', $newEId)
                    ->where('id', '!=', $animal->id)
                    ->first();
                if ($existing) {
                    return ['success' => false, 'message' => "E-ID $newEId already exists"];
                }
                $animal->e_id = $newEId;
                $animal->save();
                break;

            case 'change_v_id':
                if (!isset($changeData['v_id'])) {
                    return ['success' => false, 'message' => 'V-ID not provided'];
                }
                $newVId = $changeData['v_id'];
                // Check uniqueness
                $existing = Animal::where('v_id', $newVId)
                    ->where('id', '!=', $animal->id)
                    ->first();
                if ($existing) {
                    return ['success' => false, 'message' => "V-ID $newVId already exists"];
                }
                $animal->v_id = $newVId;
                $animal->save();
                break;

            default:
                return ['success' => false, 'message' => 'Change type not supported for individual updates'];
        }

        return ['success' => true];
    }

    /**
     * Delete a change record (only if pending or failed)
     * DELETE /api/animal-offline-changes/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user_id = Utils::get_user_id($request);
        if ($user_id < 1) {
            return Utils::response([
                'code' => 0,
                'message' => 'User not found',
                'data' => null
            ]);
        }

        $change = AnimalOfflineChange::where('id', $id)
            ->where('changed_by_user_id', $user_id)
            ->first();

        if (!$change) {
            return Utils::response([
                'code' => 0,
                'message' => 'Change not found',
                'data' => null
            ]);
        }

        if ($change->status == 'synced') {
            return Utils::response([
                'code' => 0,
                'message' => 'Cannot delete synced change',
                'data' => null
            ]);
        }

        $change->delete();

        return Utils::response([
            'code' => 1,
            'message' => 'Change deleted successfully',
            'data' => null
        ]);
    }
}
