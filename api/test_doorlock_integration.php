<?php
/**
 * Test Doorlock Integration
 * Tests complete flow: Attendance -> Doorlock Trigger -> Response
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide warnings for clean output

echo "========================================\n";
echo "DOORLOCK INTEGRATION TEST\n";
echo "========================================\n\n";

// Setup
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/api/absen';
$_POST = [
    'token' => 'SECURE_KEY_IGASAR',
    'kode' => 'TEST123',
    'status' => 'masuk',
    'device_code' => 'KIOSK-TEST'
];

// Capture output
ob_start();

try {
    require_once __DIR__ . '/controllers/AbsensiController.php';
    $controller = new AbsensiController();
    $controller->absen();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$output = ob_get_clean();

// Display result
echo "API Response:\n";
echo str_repeat("-", 40) . "\n";
$response = json_decode($output, true);
if ($response) {
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo $output . "\n";
}
echo str_repeat("-", 40) . "\n\n";

// Check logs
echo "Attendance Log (last 3 lines):\n";
echo str_repeat("-", 40) . "\n";
if (file_exists(__DIR__ . '/logs/absen.log')) {
    $lines = file(__DIR__ . '/logs/absen.log');
    echo implode('', array_slice($lines, -3));
} else {
    echo "Log file not found\n";
}
echo str_repeat("-", 40) . "\n\n";

echo "Doorlock Log (last 3 lines):\n";
echo str_repeat("-", 40) . "\n";
if (file_exists(__DIR__ . '/logs/doorlock.log')) {
    $lines = file(__DIR__ . '/logs/doorlock.log');
    echo implode('', array_slice($lines, -3));
} else {
    echo "Log file not found\n";
}
echo str_repeat("-", 40) . "\n\n";

echo "✅ Test completed!\n";
echo "Check the Raspberry Pi UI to see if door opened.\n\n";
