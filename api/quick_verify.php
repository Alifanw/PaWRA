<?php
// Quick API verification - compact output
error_reporting(0);

$tests = [
    ['name' => 'Today Summary', 'url' => 'http://localhost/api/absen/today'],
    ['name' => 'Check Adeli', 'url' => 'http://localhost/api/absen/check/0319766798'],
    ['name' => 'Adeli History', 'url' => 'http://localhost/api/absen/employee/0319766798?days=7'],
    ['name' => 'Monthly Stats', 'url' => 'http://localhost/api/absen/stats?month=2025-11'],
    ['name' => 'History List', 'url' => 'http://localhost/api/absen/history?limit=3'],
];

$token = 'SECURE_KEY_IGASAR';

echo "ATTENDANCE API - QUICK VERIFICATION\n";
echo str_repeat("=", 50) . "\n\n";

foreach ($tests as $test) {
    echo "✓ {$test['name']}: ";
    
    $ch = curl_init($test['url']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-Token: $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if ($httpCode === 200 && isset($data['status']) && $data['status'] === 'success') {
        echo "✅ PASS\n";
    } else {
        echo "❌ FAIL (HTTP $httpCode)\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "CSV Export Test...\n";
$csvUrl = 'http://localhost/api/absen/export?start_date=2025-11-01&end_date=2025-11-30';
$ch = curl_init($csvUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-Token: $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$csv = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && strlen($csv) > 100) {
    $lines = count(explode("\n", trim($csv)));
    echo "✅ CSV Generated: $lines lines\n";
} else {
    echo "❌ CSV Failed (HTTP $httpCode)\n";
}

echo "\nAll tests completed!\n";
