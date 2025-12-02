<?php
// Direct API test bypassing web server

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Direct API Endpoint Test ===\n\n";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/absen';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Test data
$testData = [
    'token' => 'SECURE_KEY_IGASAR',
    'kode' => 'TEST123',
    'status' => 'masuk',
    'device_code' => 'KIOSK-TEST'
];

// Set up input stream
$_POST = $testData; // Fallback for controller

// Capture output
ob_start();

// Include the controller directly
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AbsensiController.php';
require_once __DIR__ . '/classes/ApiResponse.php';

try {
    // Simulate JSON input
    file_put_contents('php://input', json_encode($testData));
    
    $controller = new AbsensiController();
    
    echo "Calling absen() method...\n";
    $controller->absen();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

$output = ob_get_clean();
echo $output;
