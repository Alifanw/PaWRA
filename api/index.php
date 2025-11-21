<?php
/**
 * API Endpoint untuk Absensi Modern
 * Compatible dengan Raspberry Pi Doorlock System
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/classes/AttendanceController.php';
require_once __DIR__ . '/classes/ApiResponse.php';

// Security token
define('SECURE_TOKEN', 'SECURE_KEY_IGASAR');

// Simple routing
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Parse request
if (strpos($requestUri, '/api/absen') !== false) {
    if ($requestMethod === 'POST') {
        handleAbsensi();
    } else {
        ApiResponse::error('Method not allowed', 405);
    }
} else {
    ApiResponse::error('Endpoint not found', 404);
}

/**
 * Handle absensi request
 */
function handleAbsensi()
{
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Fallback to POST data
    if (!$input) {
        $input = $_POST;
    }

    // Validasi token
    $token = $input['token'] ?? '';
    if ($token !== SECURE_TOKEN) {
        ApiResponse::error('Unauthorized - Invalid token', 403);
    }

    // Get data
    $kode = $input['kode'] ?? ($input['kodes'] ?? '');
    $status = $input['status'] ?? '';
    $deviceCode = $input['device_code'] ?? 'KIOSK-01';

    // Process attendance
    $controller = new AttendanceController();
    $controller->processAttendance($kode, $status, $deviceCode);
}
