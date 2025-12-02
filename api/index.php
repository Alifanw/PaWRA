<?php
/**
 * API Endpoint untuk Absensi Modern
 * Compatible dengan Raspberry Pi Doorlock System
 * Professional OOP Architecture
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

require_once __DIR__ . '/controllers/AbsensiController.php';
require_once __DIR__ . '/classes/ApiResponse.php';

// Simple routing
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Initialize controller
$controller = new AbsensiController();

// Route requests
if (preg_match('/\/api\/absen\/employee\/[^\/]+/', $requestUri)) {
    // GET /api/absen/employee/{code}
    if ($requestMethod === 'GET') {
        $controller->employeeHistory();
    } else {
        ApiResponse::error('Method not allowed. Use GET for /api/absen/employee/{code}', 405);
    }
} elseif (preg_match('/\/api\/absen\/check\/[^\/]+/', $requestUri)) {
    // GET /api/absen/check/{code}
    if ($requestMethod === 'GET') {
        $controller->check();
    } else {
        ApiResponse::error('Method not allowed. Use GET for /api/absen/check/{code}', 405);
    }
} elseif (strpos($requestUri, '/api/absen/today') !== false) {
    // GET /api/absen/today
    if ($requestMethod === 'GET') {
        $controller->today();
    } else {
        ApiResponse::error('Method not allowed. Use GET for /api/absen/today', 405);
    }
} elseif (strpos($requestUri, '/api/absen/stats') !== false) {
    // GET /api/absen/stats
    if ($requestMethod === 'GET') {
        $controller->stats();
    } else {
        ApiResponse::error('Method not allowed. Use GET for /api/absen/stats', 405);
    }
} elseif (strpos($requestUri, '/api/absen/export') !== false) {
    // GET /api/absen/export
    if ($requestMethod === 'GET') {
        $controller->export();
    } else {
        ApiResponse::error('Method not allowed. Use GET for /api/absen/export', 405);
    }
} elseif (strpos($requestUri, '/api/absen/history') !== false) {
    // GET /api/absen/history
    if ($requestMethod === 'GET') {
        $controller->history();
    } else {
        ApiResponse::error('Method not allowed. Use GET for /api/absen/history', 405);
    }
} elseif (strpos($requestUri, '/api/absen') !== false) {
    // POST /api/absen
    if ($requestMethod === 'POST') {
        $controller->absen();
    } else {
        ApiResponse::error('Method not allowed. Use POST for /api/absen', 405);
    }
} else {
    ApiResponse::error('Endpoint not found', 404);
}
