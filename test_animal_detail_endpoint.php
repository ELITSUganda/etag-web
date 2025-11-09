<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\V2ApiMainController;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "TESTING ANIMAL DETAIL ENDPOINT\n";
echo "========================================================\n\n";

// Create controller instance
$controller = new V2ApiMainController();

// Test with animal ID 1
$request = new Request();
$animalId = 1;

echo "Testing with Animal ID: $animalId\n";
echo "--------------------------------------------------------\n\n";

try {
    $startTime = microtime(true);
    $response = $controller->animal_detail($request, $animalId);
    $endTime = microtime(true);
    
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    // Get response content
    $content = $response->getData(true);
    
    echo "✅ RESPONSE STATUS: " . ($content['code'] ?? 'N/A') . "\n";
    echo "✅ RESPONSE TIME: {$responseTime}ms\n";
    echo "✅ MESSAGE: " . ($content['message'] ?? 'N/A') . "\n\n";
    
    if (isset($content['data'])) {
        $data = $content['data'];
        
        echo "DATA STRUCTURE VALIDATION:\n";
        echo "--------------------------------------------------------\n";
        
        // Check basic_info
        if (isset($data['basic_info'])) {
            echo "✅ basic_info: Present\n";
            echo "   - Animal ID: " . ($data['basic_info']['id'] ?? 'N/A') . "\n";
            echo "   - V-ID: " . ($data['basic_info']['v_id'] ?? 'N/A') . "\n";
            echo "   - Type: " . ($data['basic_info']['type'] ?? 'N/A') . "\n";
            echo "   - Breed: " . ($data['basic_info']['breed'] ?? 'N/A') . "\n";
            echo "   - Sex: " . ($data['basic_info']['sex'] ?? 'N/A') . "\n";
        } else {
            echo "❌ basic_info: Missing\n";
        }
        
        // Check location
        if (isset($data['location'])) {
            echo "✅ location: Present\n";
            echo "   - Farm: " . ($data['location']['farm_name'] ?? 'N/A') . "\n";
        } else {
            echo "❌ location: Missing\n";
        }
        
        // Check relationships
        if (isset($data['relationships'])) {
            echo "✅ relationships: Present\n";
            echo "   - Has Mother: " . ($data['relationships']['has_parent'] ? 'Yes' : 'No') . "\n";
            if (isset($data['relationships']['mother'])) {
                echo "   - Mother V-ID: " . ($data['relationships']['mother']['v_id'] ?? 'N/A') . "\n";
            }
            if (isset($data['relationships']['sire'])) {
                echo "   - Sire V-ID: " . ($data['relationships']['sire']['v_id'] ?? 'N/A') . "\n";
            }
            echo "   - Offspring Count: " . ($data['relationships']['offspring_count'] ?? 0) . "\n";
            echo "   - Offspring List: " . count($data['relationships']['offspring'] ?? []) . " items\n";
        } else {
            echo "❌ relationships: Missing\n";
        }
        
        // Check photos
        if (isset($data['photos'])) {
            echo "✅ photos: Present (" . count($data['photos']) . " photos)\n";
            if (count($data['photos']) > 0) {
                echo "   - First photo URL: " . (isset($data['photos'][0]['thumbnail_url']) ? 'Valid' : 'Missing') . "\n";
            }
        } else {
            echo "❌ photos: Missing\n";
        }
        
        // Check events
        if (isset($data['events'])) {
            echo "✅ events: Present\n";
            echo "   - Total Events: " . ($data['events']['total_count'] ?? 0) . "\n";
            echo "   - Latest Events: " . count($data['events']['latest'] ?? []) . " items\n";
            if (isset($data['events']['by_category'])) {
                echo "   - Milking Events: " . count($data['events']['by_category']['milking'] ?? []) . "\n";
                echo "   - Weight Events: " . count($data['events']['by_category']['weight'] ?? []) . "\n";
                echo "   - Breeding Events: " . count($data['events']['by_category']['breeding'] ?? []) . "\n";
                echo "   - Treatment Events: " . count($data['events']['by_category']['treatment'] ?? []) . "\n";
            }
        } else {
            echo "❌ events: Missing\n";
        }
        
        // Check health
        if (isset($data['health'])) {
            echo "✅ health: Present\n";
            echo "   - Is Sick: " . ($data['health']['is_sick'] ? 'Yes' : 'No') . "\n";
            echo "   - Is Pregnant: " . ($data['health']['is_pregnant'] ? 'Yes' : 'No') . "\n";
            echo "   - Vaccinations: " . count($data['health']['vaccinations'] ?? []) . " records\n";
            echo "   - Diseases: " . count($data['health']['diseases'] ?? []) . " records\n";
        } else {
            echo "❌ health: Missing\n";
        }
        
        // Check performance
        if (isset($data['performance'])) {
            echo "✅ performance: Present\n";
            if (isset($data['performance']['milk_production'])) {
                $milk = $data['performance']['milk_production'];
                echo "   - Total Milk Records: " . ($milk['total_records'] ?? 0) . "\n";
                echo "   - Total Liters: " . ($milk['total_liters'] ?? 0) . "L\n";
                echo "   - Average per Session: " . ($milk['average_per_session'] ?? 0) . "L\n";
            }
            if (isset($data['performance']['weight_tracking'])) {
                $weight = $data['performance']['weight_tracking'];
                echo "   - Total Weight Records: " . ($weight['total_records'] ?? 0) . "\n";
                echo "   - Current Weight: " . ($weight['current_weight'] ?? 0) . "kg\n";
                echo "   - Weight Gain: " . ($weight['weight_gain'] ?? 0) . "kg\n";
            }
        } else {
            echo "❌ performance: Missing\n";
        }
        
        // Check additional_info
        if (isset($data['additional_info'])) {
            echo "✅ additional_info: Present\n";
        } else {
            echo "❌ additional_info: Missing\n";
        }
        
        echo "\n--------------------------------------------------------\n";
        echo "FULL JSON RESPONSE (first 2000 chars):\n";
        echo "--------------------------------------------------------\n";
        $jsonResponse = json_encode($content, JSON_PRETTY_PRINT);
        echo substr($jsonResponse, 0, 2000) . "...\n\n";
        
        echo "========================================================\n";
        echo "✅ TEST COMPLETED SUCCESSFULLY\n";
        echo "Response Time: {$responseTime}ms\n";
        echo "========================================================\n";
        
    } else {
        echo "❌ ERROR: No data in response\n";
        print_r($content);
    }
    
} catch (Exception $e) {
    echo "❌ ERROR OCCURRED:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString() . "\n";
}
