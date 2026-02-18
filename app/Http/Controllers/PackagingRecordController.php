<?php

namespace App\Http\Controllers;

use App\Models\PackagingRecord;
use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use App\Models\Animal;
use App\Models\Utils;
use Encore\Admin\Auth\Database\Administrator;
use App\Services\PackagingRecordPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PackagingRecordController extends Controller
{
    /**
     * Get list of packaging records
     * 
     * GET /api/packaging-records
     */
    public function index(Request $r)
    {
        $user_id = Utils::get_user_id($r);
        $u = Administrator::find($user_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ]);
        }

        $query = PackagingRecord::with(['slaughterRecord', 'animal', 'packager'])
            ->where('packaged_by', $user_id);

        // Apply filters
        if ($r->has('slaughter_record_id') && !empty($r->slaughter_record_id)) {
            $query->where('slaughter_record_id', $r->slaughter_record_id);
        }

        if ($r->has('package_type') && !empty($r->package_type)) {
            $query->where('package_type', $r->package_type);
        }

        if ($r->has('status') && !empty($r->status)) {
            $query->where('status', $r->status);
        }

        if ($r->has('date_from') && !empty($r->date_from)) {
            $query->whereDate('packaging_date', '>=', $r->date_from);
        }

        if ($r->has('date_to') && !empty($r->date_to)) {
            $query->whereDate('packaging_date', '<=', $r->date_to);
        }

        // Search by V-ID, E-ID, or package code
        if ($r->has('search') && !empty($r->search)) {
            $search = $r->search;
            $query->where(function ($q) use ($search) {
                $q->where('v_id', 'LIKE', "%{$search}%")
                    ->orWhere('e_id', 'LIKE', "%{$search}%")
                    ->orWhere('barcode', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT('PKG-', LPAD(id, 6, '0')) LIKE ?", ["%{$search}%"]);
            });
        }

        // Get all records without pagination (consistent with other endpoints)
        $records = $query->orderBy('id', 'desc')->get();

        return Utils::response([
            'status' => 1,
            'message' => 'Success',
            'data' => $records,
        ]);
    }

    /**
     * Get single packaging record by ID
     * 
     * GET /api/packaging-records/{id}
     */
    public function show(Request $r, $id)
    {
        $user_id = Utils::get_user_id($r);
        $u = Administrator::find($user_id);
        
        if ($u == null) {
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ]);
        }

        $record = PackagingRecord::with([
            'slaughterRecord', 
            'animal', 
            'distributionRecord', 
            'packager'
        ])->find($id);

        if (!$record) {
            return Utils::response([
                'status' => 0,
                'message' => "Packaging record with ID '{$id}' not found.",
            ]);
        }

        // Add cut breakdown to response
        $record->cut_breakdown = $record->getCutBreakdown();
        $record->days_until_expiry = $record->days_until_expiry;

        return Utils::response([
            'status' => 1,
            'message' => 'Success',
            'data' => $record,
        ]);
    }

    /**
     * Create new packaging record
     * 
     * POST /api/packaging-records/create
     */
    public function store(Request $r)
    {
        // Validation rules
        $validator = Validator::make($r->all(), [
            'administrator_id' => 'required|exists:admin_users,id',
            'slaughter_record_id' => 'required|exists:slaughter_records,id',
            'slaughter_distribution_record_id' => 'nullable|exists:slaughter_distribution_records,id',
            'package_type' => 'required|in:Prime Cut,Primal Cut,Offal',
            'packaging_date' => 'required|date',
            'packages' => 'required|array|min:1',
            'packages.*.cut_name' => 'required|string',
            'packages.*.weight' => 'required|numeric|min:0.01|max:9999.99',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return Utils::response([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        // Check slaughter record exists
        $slaughterRecord = SlaughterRecord::find($r->slaughter_record_id);
        if (!$slaughterRecord) {
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter record with ID '{$r->slaughter_record_id}' not found.",
            ]);
        }

        try {
            DB::beginTransaction();

            // Prepare data
            $data = [
                'slaughter_record_id' => $r->slaughter_record_id,
                'slaughter_distribution_record_id' => $r->slaughter_distribution_record_id,
                'package_type' => $r->package_type,
                'packaging_date' => $r->packaging_date,
                'expiry_date' => null, // No longer using expiry date
                'packaged_by' => $r->administrator_id,
                'notes' => $r->notes,
            ];

            // Get animal info from slaughter record
            $data['v_id'] = $slaughterRecord->v_id;
            $data['e_id'] = $slaughterRecord->e_id;
            $data['lhc'] = $slaughterRecord->lhc;
            $data['breed'] = $slaughterRecord->breed;
            $data['sex'] = $slaughterRecord->sex;

            // Get barcode/QR code if distribution record provided
            if ($r->slaughter_distribution_record_id) {
                $distributionRecord = SlaughterDistributionRecord::find($r->slaughter_distribution_record_id);
                if ($distributionRecord) {
                    $data['barcode'] = $distributionRecord->bar_code;
                    $data['qr_code_link'] = $distributionRecord->qr_code;
                    $data['animal_id'] = $distributionRecord->animal_id;
                }
            }

            // Process packages array
            $cutFieldMap = $this->getCutFieldMapping();
            
            // Initialize all cut fields to 0
            if ($r->package_type === 'Prime Cut' || $r->package_type === 'Primal Cut') {
                $primeCuts = PackagingRecord::getPrimeCutFields();
                foreach ($primeCuts as $cut) {
                    $data[$cut] = 0;
                }
            } else {
                $offals = PackagingRecord::getOffalFields();
                foreach ($offals as $offal) {
                    $data[$offal] = 0;
                }
            }
            
            // Add weights from packages array
            $seenCuts = [];
            foreach ($r->packages as $package) {
                $cutName = $package['cut_name'];
                $weight = $package['weight'];
                
                // Map cut name to field name
                $fieldName = $cutFieldMap[$cutName] ?? null;
                if ($fieldName && isset($data[$fieldName])) {
                    // Use latest weight if same cut submitted twice
                    $data[$fieldName] = isset($seenCuts[$fieldName])
                        ? $data[$fieldName] + $weight
                        : $weight;
                    $seenCuts[$fieldName] = true;
                }
            }

            // Create packaging record
            $packagingRecord = PackagingRecord::create($data);

            // Validate that at least one weight was entered
            if (!$packagingRecord->validateWeights()) {
                DB::rollBack();
                return Utils::response([
                    'status' => 0,
                    'message' => "Please enter at least one weight value for the {$r->package_type}.",
                ]);
            }

            DB::commit();

            // Generate PDF
            try {
                $pdfService = new PackagingRecordPdfService();
                $pdfPath = $pdfService->generateLabel($packagingRecord);
                $packagingRecord->refresh(); // Reload to get updated pdf fields
            } catch (\Exception $e) {
                \Log::error('PDF generation failed for PackagingRecord #' . $packagingRecord->id . ': ' . $e->getMessage());
                // Continue even if PDF generation fails
            }

            return Utils::response([
                'status' => 1,
                'message' => 'Packaging record created successfully.',
                'data' => [
                    'id' => $packagingRecord->id,
                    'package_code' => $packagingRecord->package_code,
                    'total_weight' => $packagingRecord->total_weight,
                    'pdf_generated' => $packagingRecord->pdf_generated,
                    'pdf_url' => $packagingRecord->pdf_url,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to create packaging record: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update existing packaging record
     * 
     * POST /api/packaging-records/update
     */
    public function update(Request $r)
    {
        // Validation rules
        $validator = Validator::make($r->all(), [
            'administrator_id' => 'required|exists:admin_users,id',
            'id' => 'required|exists:packaging_records,id',
            'package_type' => 'required|in:Prime Cut,Primal Cut,Offal',
            'packaging_date' => 'required|date',
            'shelf_life_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Add weight validation rules dynamically
        if ($r->package_type === 'Prime Cut' || $r->package_type === 'Primal Cut') {
            $primeCuts = PackagingRecord::getPrimeCutFields();
            foreach ($primeCuts as $cut) {
                $validator->addRules([$cut => 'nullable|numeric|min:0|max:9999.99']);
            }
        } else {
            $offals = PackagingRecord::getOffalFields();
            foreach ($offals as $offal) {
                $validator->addRules([$offal => 'nullable|numeric|min:0|max:9999.99']);
            }
        }

        if ($validator->fails()) {
            return Utils::response([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        $packagingRecord = PackagingRecord::find($r->id);
        if (!$packagingRecord) {
            return Utils::response([
                'status' => 0,
                'message' => "Packaging record with ID '{$r->id}' not found.",
            ]);
        }

        // Prevent editing if already sold
        if ($packagingRecord->status === 'Sold') {
            return Utils::response([
                'status' => 0,
                'message' => 'Cannot edit a sold packaging record.',
            ]);
        }

        try {
            DB::beginTransaction();

            // Update basic fields
            $packagingRecord->package_type = $r->package_type;
            $packagingRecord->packaging_date = $r->packaging_date;
            $packagingRecord->expiry_date = Carbon::parse($r->packaging_date)->addDays($r->shelf_life_days);
            $packagingRecord->notes = $r->notes;

            // Reset all weights to 0 first
            if ($r->package_type === 'Prime Cut' || $r->package_type === 'Primal Cut') {
                $primeCuts = PackagingRecord::getPrimeCutFields();
                foreach ($primeCuts as $cut) {
                    $packagingRecord->$cut = 0;
                }
                // Then set new values
                foreach ($primeCuts as $cut) {
                    $packagingRecord->$cut = $r->$cut ?? 0;
                }
            } else {
                $offals = PackagingRecord::getOffalFields();
                foreach ($offals as $offal) {
                    $packagingRecord->$offal = 0;
                }
                // Then set new values
                foreach ($offals as $offal) {
                    $packagingRecord->$offal = $r->$offal ?? 0;
                }
            }

            $packagingRecord->save();

            // Validate that at least one weight was entered
            if (!$packagingRecord->validateWeights()) {
                DB::rollBack();
                return Utils::response([
                    'status' => 0,
                    'message' => "Please enter at least one weight value for the {$r->package_type}.",
                ]);
            }

            DB::commit();

            return Utils::response([
                'status' => 1,
                'message' => 'Packaging record updated successfully.',
                'data' => [
                    'id' => $packagingRecord->id,
                    'package_code' => $packagingRecord->package_code,
                    'total_weight' => $packagingRecord->total_weight,
                    'expiry_date' => $packagingRecord->expiry_date->format('Y-m-d'),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to update packaging record: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark packaging record as sold
     * 
     * POST /api/packaging-records/mark-sold
     */
    public function markSold(Request $r)
    {
        $validator = Validator::make($r->all(), [
            'administrator_id' => 'required|exists:admin_users,id',
            'id' => 'required|exists:packaging_records,id',
            'buyer_name' => 'nullable|string|max:255',
            'buyer_phone' => 'nullable|string|max:20',
            'sold_price' => 'nullable|numeric|min:0',
            'sold_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return Utils::response([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        $packagingRecord = PackagingRecord::find($r->id);
        if (!$packagingRecord) {
            return Utils::response([
                'status' => 0,
                'message' => "Packaging record with ID '{$r->id}' not found.",
            ]);
        }

        if ($packagingRecord->status === 'Sold') {
            return Utils::response([
                'status' => 0,
                'message' => 'This packaging record is already marked as sold.',
            ]);
        }

        try {
            $packagingRecord->status = 'Sold';
            
            // Store buyer info in notes if provided
            if ($r->buyer_name || $r->buyer_phone || $r->sold_price) {
                $saleInfo = [];
                if ($r->buyer_name) $saleInfo[] = "Buyer: {$r->buyer_name}";
                if ($r->buyer_phone) $saleInfo[] = "Phone: {$r->buyer_phone}";
                if ($r->sold_price) $saleInfo[] = "Price: UGX " . number_format($r->sold_price);
                if ($r->sold_date) $saleInfo[] = "Date: {$r->sold_date}";
                
                $currentNotes = $packagingRecord->notes ?? '';
                $packagingRecord->notes = $currentNotes . "\n\nSOLD:\n" . implode("\n", $saleInfo);
            }
            
            $packagingRecord->save();

            return Utils::response([
                'status' => 1,
                'message' => 'Packaging record marked as sold successfully.',
                'data' => $packagingRecord,
            ]);

        } catch (\Exception $e) {
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to mark as sold: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete packaging record
     * 
     * POST /api/packaging-records/delete
     */
    public function destroy(Request $r)
    {
        $validator = Validator::make($r->all(), [
            'administrator_id' => 'required|exists:admin_users,id',
            'id' => 'required|exists:packaging_records,id',
        ]);

        if ($validator->fails()) {
            return Utils::response([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        $packagingRecord = PackagingRecord::find($r->id);
        if (!$packagingRecord) {
            return Utils::response([
                'status' => 0,
                'message' => "Packaging record with ID '{$r->id}' not found.",
            ]);
        }

        // Prevent deletion if sold
        if ($packagingRecord->status === 'Sold') {
            return Utils::response([
                'status' => 0,
                'message' => 'Cannot delete a sold packaging record.',
            ]);
        }

        try {
            // Delete PDF file if exists
            if ($packagingRecord->pdf_file_path && file_exists(public_path($packagingRecord->pdf_file_path))) {
                unlink(public_path($packagingRecord->pdf_file_path));
            }

            $packagingRecord->delete();

            return Utils::response([
                'status' => 1,
                'message' => 'Packaging record deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to delete packaging record: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get packaging records for a specific slaughter record
     * 
     * GET /api/slaughter-records/{id}/packaging-records
     */
    public function getBySlaughterRecord(Request $r, $id)
    {
        \Log::info("📦 [getBySlaughterRecord] Called with ID: {$id}");
        
        $user_id = Utils::get_user_id($r);
        \Log::info("📦 [getBySlaughterRecord] User ID: {$user_id}");
        
        $u = Administrator::find($user_id);
        
        if ($u == null) {
            \Log::warning("📦 [getBySlaughterRecord] User not found");
            return Utils::response([
                'status' => 0,
                'message' => 'User not found.',
            ]);
        }

        $slaughterRecord = SlaughterRecord::find($id);
        if (!$slaughterRecord) {
            \Log::warning("📦 [getBySlaughterRecord] Slaughter record {$id} not found");
            return Utils::response([
                'status' => 0,
                'message' => "Slaughter record with ID '{$id}' not found.",
            ]);
        }

        $packagingRecords = PackagingRecord::with(['packager'])
            ->where('slaughter_record_id', $id)
            ->orderBy('id', 'desc')
            ->get();

        \Log::info("📦 [getBySlaughterRecord] Found {$packagingRecords->count()} packaging records");
        \Log::info("📦 [getBySlaughterRecord] Total weight: {$packagingRecords->sum('total_weight')}");

        $response = Utils::response([
            'status' => 1,
            'message' => 'Success',
            'data' => [
                'slaughter_record' => $slaughterRecord,
                'packaging_records' => $packagingRecords,
                'total_packages' => $packagingRecords->count(),
                'total_weight' => $packagingRecords->sum('total_weight'),
            ],
        ]);
        
        \Log::info("📦 [getBySlaughterRecord] Response code: " . $response['code']);
        
        return $response;
    }

    /**
     * Generate or regenerate PDF label for packaging record
     * 
     * POST /api/packaging-records/generate-pdf
     */
    public function generatePdf(Request $r)
    {
        $validator = Validator::make($r->all(), [
            'administrator_id' => 'required|exists:admin_users,id',
            'id' => 'required|exists:packaging_records,id',
        ]);

        if ($validator->fails()) {
            return Utils::response([
                'status' => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        $packagingRecord = PackagingRecord::find($r->id);
        if (!$packagingRecord) {
            return Utils::response([
                'status' => 0,
                'message' => "Packaging record with ID '{$r->id}' not found.",
            ]);
        }

        try {
            $pdfService = new PackagingRecordPdfService();
            
            // Regenerate if PDF already exists, otherwise generate new
            if ($packagingRecord->pdf_generated === 'Yes') {
                $pdfPath = $pdfService->regenerateLabel($packagingRecord);
            } else {
                $pdfPath = $pdfService->generateLabel($packagingRecord);
            }
            
            $packagingRecord->refresh();

            return Utils::response([
                'status' => 1,
                'message' => 'PDF label generated successfully.',
                'data' => [
                    'id' => $packagingRecord->id,
                    'package_code' => $packagingRecord->package_code,
                    'pdf_generated' => $packagingRecord->pdf_generated,
                    'pdf_url' => $packagingRecord->pdf_url,
                    'pdf_path' => $pdfPath,
                ],
            ]);

        } catch (\Exception $e) {
            return Utils::response([
                'status' => 0,
                'message' => 'Failed to generate PDF: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Map cut names from mobile app to database field names
     */
    private function getCutFieldMapping()
    {
        return [
            // Prime Cuts
            'Beef Boneless' => 'beef_boneless',
            'Beef Stew' => 'beef_stew',
            'Bones' => 'bones',
            'Brisket' => 'brisket',
            'Chops' => 'chops',
            'Chuck Ribs' => 'chuck_ribs',
            'Family Steak' => 'family_steak',
            'Fore Rib' => 'fore_rib',
            'Leg Cut' => 'leg_cut',
            'Middle Rib' => 'middle_rib',
            'Minced Meat' => 'minced_meat',
            'Neck' => 'neck',
            'Ossubucco' => 'ossubucco',
            'Oxtail' => 'oxtail',
            'Ribs' => 'ribs',
            'Shin' => 'shin',
            'Staff Meat' => 'staff_meat',
            'Thick Flank' => 'thick_flank',
            'Fillet' => 'fillet',
            'Rib Eye' => 'rib_eye',
            'Rolled Loin' => 'rolled_loin',
            'Rump' => 'rump',
            'Silver Side' => 'silver_side',
            'Sirloin/Striploin' => 'sirloin_striploin',
            'T-Bone' => 't_bone',
            'Topside/Beef Roast' => 'topside_beef_roast',
            'Veal Steak' => 'veal_steak',
            
            // Offal Cuts
            'Heart' => 'heart',
            'Kidneys' => 'kidneys',
            'Liver' => 'liver',
            'Tongue' => 'tongue',
            'Lungs' => 'lungs',
            'Tripe' => 'tripe',
            'Tail' => 'tail',
            'Head' => 'head',
            'Feet' => 'feet',
            'Testicles' => 'testicles',
        ];
    }
}
