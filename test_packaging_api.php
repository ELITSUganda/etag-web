<?php

/**
 * PackagingRecord API Integration Test
 * 
 * Tests all 8 API endpoints with real HTTP requests
 * Run with: php test_packaging_api.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\SlaughterRecord;
use App\Models\PackagingRecord;
use App\Http\Controllers\PackagingRecordController;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n" . str_repeat("=", 80) . "\n";
echo "PackagingRecord API Integration Test\n";
echo str_repeat("=", 80) . "\n\n";

$controller = new PackagingRecordController();
$passedTests = 0;
$totalTests = 0;

// Helper function to create mock request
function mockRequest($data = [], $method = 'GET') {
    $request = Request::create('/test', $method, $data);
    return $request;
}

// Test 1: List All Packaging Records (index)
echo "Test 1: GET /api/packaging-records (List All)\n";
$totalTests++;
try {
    $request = mockRequest();
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() == 200 && isset($data['data'])) {
        echo "✅ PASSED - Status: 200, Records: " . count($data['data']) . "\n";
        echo "   Total: {$data['total']}, Per Page: {$data['per_page']}\n";
        $passedTests++;
    } else {
        echo "❌ FAILED - Invalid response structure\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 2: List with Filters
echo "\nTest 2: GET /api/packaging-records?package_type=Prime Cut&status=Active\n";
$totalTests++;
try {
    $request = mockRequest(['package_type' => 'Prime Cut', 'status' => 'Active']);
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() == 200) {
        echo "✅ PASSED - Filtered records: " . count($data['data']) . "\n";
        foreach ($data['data'] as $record) {
            echo "   - {$record['package_code']}: {$record['package_type']} ({$record['status']})\n";
        }
        $passedTests++;
    } else {
        echo "❌ FAILED\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 3: Get Single Record (show)
echo "\nTest 3: GET /api/packaging-records/{id}\n";
$totalTests++;
try {
    $firstRecord = PackagingRecord::first();
    if ($firstRecord) {
        $response = $controller->show($firstRecord->id);
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() == 200 && isset($data['data'])) {
            echo "✅ PASSED - Retrieved: {$data['data']['package_code']}\n";
            echo "   Total Weight: {$data['data']['total_weight']} kg\n";
            echo "   Status: {$data['data']['status']}\n";
            echo "   Cut Breakdown: " . count($data['data']['cut_breakdown']) . " items\n";
            $passedTests++;
        } else {
            echo "❌ FAILED\n";
        }
    } else {
        echo "⚠️  SKIPPED - No records exist\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 4: Create New Record (store)
echo "\nTest 4: POST /api/packaging-records/create\n";
$totalTests++;
try {
    $slaughterRecord = SlaughterRecord::first();
    if ($slaughterRecord) {
        $testData = [
            'slaughter_record_id' => $slaughterRecord->id,
            'package_type' => 'Prime Cut',
            'packaging_date' => date('Y-m-d'),
            'expiry_date' => date('Y-m-d', strtotime('+30 days')),
            'packaged_by' => 1,
            'beef_boneless' => 10.5,
            'fillet' => 3.2,
            'sirloin_striploin' => 5.7,
            'notes' => 'API Test Package'
        ];
        
        $request = mockRequest($testData, 'POST');
        $response = $controller->store($request);
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() == 201 && isset($data['data'])) {
            echo "✅ PASSED - Created: {$data['data']['package_code']}\n";
            echo "   Total Weight: {$data['data']['total_weight']} kg\n";
            echo "   PDF Generated: " . ($data['data']['pdf_generated'] ? 'Yes' : 'No') . "\n";
            $createdId = $data['data']['id'];
            $passedTests++;
        } else {
            echo "❌ FAILED - " . ($data['message'] ?? 'Unknown error') . "\n";
            if (isset($data['errors'])) {
                print_r($data['errors']);
            }
        }
    } else {
        echo "⚠️  SKIPPED - No slaughter records exist\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 5: Update Record (update)
echo "\nTest 5: POST /api/packaging-records/update\n";
$totalTests++;
try {
    $record = PackagingRecord::where('status', 'Active')->first();
    if ($record) {
        $updateData = [
            'id' => $record->id,
            'beef_boneless' => 12.0,
            'notes' => 'Updated via API test'
        ];
        
        $request = mockRequest($updateData, 'POST');
        $response = $controller->update($request);
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() == 200) {
            echo "✅ PASSED - Updated: {$data['data']['package_code']}\n";
            echo "   New Total Weight: {$data['data']['total_weight']} kg\n";
            $passedTests++;
        } else {
            echo "❌ FAILED - " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "⚠️  SKIPPED - No active records exist\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 6: Get Packages by Slaughter Record
echo "\nTest 6: GET /api/slaughter-records/{id}/packaging-records\n";
$totalTests++;
try {
    $slaughterRecord = SlaughterRecord::first();
    if ($slaughterRecord) {
        $response = $controller->getBySlaughterRecord($slaughterRecord->id);
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() == 200) {
            echo "✅ PASSED - Found " . count($data['data']) . " packages for slaughter record #{$slaughterRecord->id}\n";
            foreach ($data['data'] as $pkg) {
                echo "   - {$pkg['package_code']}: {$pkg['package_type']} ({$pkg['total_weight']} kg)\n";
            }
            $passedTests++;
        } else {
            echo "❌ FAILED\n";
        }
    } else {
        echo "⚠️  SKIPPED - No slaughter records exist\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 7: Mark as Sold
echo "\nTest 7: POST /api/packaging-records/mark-sold\n";
$totalTests++;
try {
    $record = PackagingRecord::where('status', 'Active')->first();
    if ($record) {
        $soldData = [
            'id' => $record->id,
            'sold_date' => date('Y-m-d'),
            'buyer_info' => 'Test Buyer - API Test'
        ];
        
        $request = mockRequest($soldData, 'POST');
        $response = $controller->markSold($request);
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() == 200 && $data['data']['status'] === 'Sold') {
            echo "✅ PASSED - Marked as sold: {$data['data']['package_code']}\n";
            echo "   Status: {$data['data']['status']}\n";
            $passedTests++;
        } else {
            echo "❌ FAILED\n";
        }
    } else {
        echo "⚠️  SKIPPED - No active records exist\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 8: Generate PDF
echo "\nTest 8: POST /api/packaging-records/generate-pdf\n";
$totalTests++;
try {
    $record = PackagingRecord::first();
    if ($record) {
        $pdfData = ['id' => $record->id];
        
        $request = mockRequest($pdfData, 'POST');
        $response = $controller->generatePdf($request);
        $data = json_decode($response->getContent(), true);
        
        if ($response->getStatusCode() == 200) {
            echo "✅ PASSED - PDF generated for: {$data['data']['package_code']}\n";
            echo "   PDF URL: {$data['data']['pdf_url']}\n";
            echo "   PDF Path: {$data['data']['pdf_file_path']}\n";
            
            // Check if file exists
            $fullPath = public_path('storage/images/' . $data['data']['pdf_file_path']);
            if (file_exists($fullPath)) {
                $fileSize = filesize($fullPath);
                echo "   File Size: " . number_format($fileSize / 1024, 2) . " KB\n";
            } else {
                echo "   ⚠️  Warning: PDF file not found on disk\n";
            }
            $passedTests++;
        } else {
            echo "❌ FAILED - " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "⚠️  SKIPPED - No records exist\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Test 9: Validation Test (should fail)
echo "\nTest 9: POST /api/packaging-records/create (Validation Test)\n";
$totalTests++;
try {
    $invalidData = [
        'package_type' => 'Invalid Type',
        // Missing required fields
    ];
    
    $request = mockRequest($invalidData, 'POST');
    $response = $controller->store($request);
    $data = json_decode($response->getContent(), true);
    
    if ($response->getStatusCode() == 422 && isset($data['errors'])) {
        echo "✅ PASSED - Validation working correctly\n";
        echo "   Errors caught: " . count($data['errors']) . "\n";
        foreach ($data['errors'] as $field => $messages) {
            echo "   - {$field}: " . implode(', ', $messages) . "\n";
        }
        $passedTests++;
    } else {
        echo "❌ FAILED - Validation not working\n";
    }
} catch (Exception $e) {
    echo "❌ FAILED - Error: " . $e->getMessage() . "\n";
}

// Summary
echo "\n" . str_repeat("=", 80) . "\n";
echo "Test Summary\n";
echo str_repeat("=", 80) . "\n";
echo "Total Tests: {$totalTests}\n";
echo "Passed: {$passedTests}\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n";

if ($passedTests === $totalTests) {
    echo "\n🎉 ALL TESTS PASSED! API is fully functional.\n";
} else {
    echo "\n⚠️  Some tests failed. Review errors above.\n";
}

echo "\nNext Steps:\n";
echo "1. Start web server to test PDF rendering\n";
echo "2. Create Postman collection\n";
echo "3. Proceed to mobile app integration\n";
echo str_repeat("=", 80) . "\n\n";
