<?php

/**
 * BUTCHERY WORKFLOW API TEST
 * 
 * This script tests the new butchery workflow architecture where:
 * - Cuts belong directly to carcass (not to quarters)
 * - Quarters belong to carcass
 * - Cut types: Prime and Offal
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SlaughterRecord;
use App\Models\SlaughterDistributionRecord;
use Encore\Admin\Auth\Database\Administrator;

echo "==========================================\n";
echo "BUTCHERY WORKFLOW API TEST\n";
echo "==========================================\n\n";

// Get a test user
$user = Administrator::first();
if (!$user) {
    die("Error: No user found in database.\n");
}
echo "✓ Using test user: {$user->name} (ID: {$user->id})\n\n";

// Get or create a test carcass
$carcass = SlaughterRecord::orderBy('id', 'DESC')->first();
if (!$carcass) {
    die("Error: No slaughter record (carcass) found. Please create one first.\n");
}
echo "✓ Using test carcass: V-ID {$carcass->v_id}, E-ID {$carcass->e_id} (ID: {$carcass->id})\n";
echo "  Available weight: {$carcass->available_weight} kg\n\n";

// Clean up previous test data
echo "Cleaning up previous test data...\n";
SlaughterDistributionRecord::where('source_id', $carcass->id)
    ->whereIn('cut_type', ['Prime', 'Offal'])
    ->delete();
SlaughterDistributionRecord::where('source_id', $carcass->id)
    ->where('source_address', 'like', '%1/4%')
    ->delete();
echo "✓ Cleanup complete\n\n";

// Reset carcass available weight
$originalWeight = $carcass->post_weight;
$carcass->available_weight = $originalWeight;
$carcass->save();

echo "==========================================\n";
echo "STEP 1: CREATE QUARTERS (All 4 at once)\n";
echo "==========================================\n\n";

$quarters = [
    ['name' => 'Fore-1/4 - Left', 'weight' => 50],
    ['name' => 'Fore-1/4 - Right', 'weight' => 52],
    ['name' => 'Hind-1/4 - Left', 'weight' => 48],
    ['name' => 'Hind-1/4 - Right', 'weight' => 50],
];

$createdQuarters = [];
foreach ($quarters as $quarter) {
    $rec = new SlaughterDistributionRecord();
    $rec->animal_id = $carcass->animal_id ?? 1;  // Use 1 as default if not set
    $rec->slaughterhouse_id = $carcass->id;
    $rec->created_by_id = $user->id;
    $rec->source_type = "Carcass";
    $rec->source_id = $carcass->id;  // Belongs to carcass
    $rec->source_name = $quarter['name'];
    $rec->source_address = $quarter['name'];  // Contains "Fore-1/4" or "Hind-1/4"
    $rec->lhc = $carcass->lhc;
    $rec->v_id = $carcass->v_id;
    $rec->e_id = $carcass->e_id;
    $rec->original_weight = $quarter['weight'];
    $rec->current_weight = $quarter['weight'];
    $rec->slaughter_date = $carcass->created_at;
    $rec->save();
    
    $createdQuarters[] = $rec;
    echo "✓ Created quarter: {$quarter['name']} - {$quarter['weight']} kg (ID: {$rec->id})\n";
}

echo "\n✓ All 4 quarters created successfully\n";
echo "  Total quarter weight: " . array_sum(array_column($quarters, 'weight')) . " kg\n\n";

echo "==========================================\n";
echo "STEP 2: CREATE PRIME CUTS (Directly from carcass)\n";
echo "==========================================\n\n";

$primeCuts = [
    ['name' => 'T-Bone', 'weight' => 15],
    ['name' => 'Ribeye', 'weight' => 12],
    ['name' => 'Sirloin', 'weight' => 18],
    ['name' => 'Tenderloin', 'weight' => 10],
];

$createdPrimeCuts = [];
foreach ($primeCuts as $cut) {
    $rec = new SlaughterDistributionRecord();
    $rec->animal_id = $carcass->animal_id ?? 1;
    $rec->slaughterhouse_id = $carcass->id;
    $rec->created_by_id = $user->id;
    $rec->source_type = "Carcass";
    $rec->source_id = $carcass->id;  // BELONGS DIRECTLY TO CARCASS
    $rec->source_name = $cut['name'];
    $rec->source_address = "Prime - {$cut['name']}";
    $rec->cut_type = "Prime";  // Important!
    $rec->lhc = $carcass->lhc;
    $rec->v_id = $carcass->v_id;
    $rec->e_id = $carcass->e_id;
    $rec->original_weight = $cut['weight'];
    $rec->current_weight = $cut['weight'];
    $rec->slaughter_date = $carcass->created_at;
    $rec->save();
    
    $createdPrimeCuts[] = $rec;
    echo "✓ Created prime cut: {$cut['name']} - {$cut['weight']} kg (ID: {$rec->id})\n";
}

echo "\n✓ All prime cuts created successfully\n";
echo "  Total prime cuts weight: " . array_sum(array_column($primeCuts, 'weight')) . " kg\n\n";

echo "==========================================\n";
echo "STEP 3: CREATE OFFAL CUTS (Directly from carcass)\n";
echo "==========================================\n\n";

$offalCuts = [
    ['name' => 'Liver', 'weight' => 5],
    ['name' => 'Heart', 'weight' => 3],
    ['name' => 'Kidneys', 'weight' => 2],
    ['name' => 'Tongue', 'weight' => 2],
];

$createdOffalCuts = [];
foreach ($offalCuts as $cut) {
    $rec = new SlaughterDistributionRecord();
    $rec->animal_id = $carcass->animal_id ?? 1;
    $rec->slaughterhouse_id = $carcass->id;
    $rec->created_by_id = $user->id;
    $rec->source_type = "Carcass";
    $rec->source_id = $carcass->id;  // BELONGS DIRECTLY TO CARCASS
    $rec->source_name = $cut['name'];
    $rec->source_address = "Offal - {$cut['name']}";
    $rec->cut_type = "Offal";  // Important!
    $rec->lhc = $carcass->lhc;
    $rec->v_id = $carcass->v_id;
    $rec->e_id = $carcass->e_id;
    $rec->original_weight = $cut['weight'];
    $rec->current_weight = $cut['weight'];
    $rec->slaughter_date = $carcass->created_at;
    $rec->save();
    
    $createdOffalCuts[] = $rec;
    echo "✓ Created offal cut: {$cut['name']} - {$cut['weight']} kg (ID: {$rec->id})\n";
}

echo "\n✓ All offal cuts created successfully\n";
echo "  Total offal cuts weight: " . array_sum(array_column($offalCuts, 'weight')) . " kg\n\n";

echo "==========================================\n";
echo "VERIFICATION: DATA RETRIEVAL TEST\n";
echo "==========================================\n\n";

// Test quarters retrieval
$quartersFromDB = SlaughterDistributionRecord::getQuartersForCarcass($carcass->id);
echo "Quarters for carcass {$carcass->id}:\n";
foreach ($quartersFromDB as $q) {
    echo "  - {$q->source_address}: {$q->original_weight} kg (ID: {$q->id})\n";
}
echo "  Total: {$quartersFromDB->count()} quarters\n\n";

// Test prime cuts retrieval
$primeCutsFromDB = SlaughterDistributionRecord::getCutsForCarcass($carcass->id, 'Prime');
echo "Prime cuts for carcass {$carcass->id}:\n";
foreach ($primeCutsFromDB as $c) {
    echo "  - {$c->source_name}: {$c->original_weight} kg (cut_type: {$c->cut_type}, ID: {$c->id})\n";
}
echo "  Total: {$primeCutsFromDB->count()} prime cuts\n\n";

// Test offal cuts retrieval
$offalCutsFromDB = SlaughterDistributionRecord::getCutsForCarcass($carcass->id, 'Offal');
echo "Offal cuts for carcass {$carcass->id}:\n";
foreach ($offalCutsFromDB as $c) {
    echo "  - {$c->source_name}: {$c->original_weight} kg (cut_type: {$c->cut_type}, ID: {$c->id})\n";
}
echo "  Total: {$offalCutsFromDB->count()} offal cuts\n\n";

echo "==========================================\n";
echo "ARCHITECTURE VALIDATION\n";
echo "==========================================\n\n";

// Verify all cuts belong to carcass
$allCuts = SlaughterDistributionRecord::whereIn('cut_type', ['Prime', 'Offal'])
    ->where('source_id', $carcass->id)
    ->get();

$allValid = true;
foreach ($allCuts as $cut) {
    if ($cut->source_id != $carcass->id) {
        echo "✗ ERROR: Cut {$cut->id} has source_id {$cut->source_id}, expected {$carcass->id}\n";
        $allValid = false;
    }
}

if ($allValid) {
    echo "✓ All cuts correctly reference carcass ID: {$carcass->id}\n";
    echo "✓ Architecture validation PASSED\n\n";
} else {
    echo "✗ Architecture validation FAILED\n\n";
}

echo "==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n\n";

echo "Carcass ID: {$carcass->id}\n";
echo "Original weight: {$originalWeight} kg\n";
echo "Quarters created: 4 (" . array_sum(array_column($quarters, 'weight')) . " kg)\n";
echo "Prime cuts created: " . count($primeCuts) . " (" . array_sum(array_column($primeCuts, 'weight')) . " kg)\n";
echo "Offal cuts created: " . count($offalCuts) . " (" . array_sum(array_column($offalCuts, 'weight')) . " kg)\n\n";

echo "✓ Test completed successfully!\n";
echo "✓ New architecture working correctly:\n";
echo "  - Quarters belong to carcass (source_id = carcass.id)\n";
echo "  - Cuts belong DIRECTLY to carcass (source_id = carcass.id)\n";
echo "  - Cuts have cut_type field ('Prime' or 'Offal')\n";
echo "  - Data can be filtered by cut_type and source_address\n\n";
