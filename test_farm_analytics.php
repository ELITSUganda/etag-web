<?php

/**
 * Farm Analytics API Test Script
 * Tests the new graph endpoints we just implemented
 */

// Get the first farm ID for testing
$farm_id = 1; // Using farm ID 1 for testing

echo "=== Farm Analytics Dashboard API Test ===\n";
echo "Testing Farm ID: $farm_id\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

// Base URL for local testing
$base_url = 'http://localhost:8888/etag-web/public/api';

// Function to make API request and format output
function test_endpoint($url, $name) {
    echo "📊 Testing $name\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: $http_code\n";
    
    if ($http_code == 200) {
        $data = json_decode($response, true);
        if ($data && isset($data['success']) && $data['success']) {
            echo "✅ SUCCESS\n";
            echo "Data Sample: " . substr(json_encode($data['data'], JSON_PRETTY_PRINT), 0, 200) . "...\n";
        } else {
            echo "❌ API ERROR: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ HTTP ERROR: $http_code\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
    }
    
    echo "\n" . str_repeat("-", 60) . "\n\n";
}

// Test all endpoints
test_endpoint("$base_url/farm-analysis/$farm_id/kpi-grid", "KPI Dashboard Grid");
test_endpoint("$base_url/farm-analysis/$farm_id/milk-trend?days=30", "Milk Production Trend");
test_endpoint("$base_url/farm-analysis/$farm_id/reproduction-funnel", "Reproduction Funnel");
test_endpoint("$base_url/farm-analysis/$farm_id/demographics", "Herd Demographics");

echo "=== Test Complete ===\n";
