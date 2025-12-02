<?php
/**
 * Mock Doorlock Server - PHP Version
 * Simple HTTP server to test doorlock integration
 * Run: php -S localhost:5000 mock_doorlock_server.php
 */

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Parse request
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

// Valid token
$VALID_TOKEN = 'Bearer SECURE_KEY_IGASAR';

// Log request
error_log("[MOCK DOORLOCK] {$method} {$uri}");

// Route: POST /door/open
if ($method === 'POST' && strpos($uri, '/door/open') !== false) {
    
    // Check authorization
    if ($authHeader !== $VALID_TOKEN) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid token',
            'received' => $authHeader
        ]);
        exit;
    }
    
    $delay = $input['delay'] ?? 3;
    
    error_log("[MOCK] ✅ Door opened! Delay: {$delay}s");
    
    echo json_encode([
        'status' => 'success',
        'message' => "Door opened for {$delay} seconds",
        'timestamp' => date('Y-m-d H:i:s'),
        'mock' => true
    ]);
    exit;
}

// Route: GET /door/status
if ($method === 'GET' && strpos($uri, '/door/status') !== false) {
    echo json_encode([
        'status' => 'success',
        'door_status' => 'closed',
        'server' => 'mock_php',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Route: GET /health
if ($method === 'GET' && strpos($uri, '/health') !== false) {
    echo json_encode([
        'status' => 'healthy',
        'server' => 'mock_doorlock_php',
        'version' => '1.0'
    ]);
    exit;
}

// 404
http_response_code(404);
echo json_encode([
    'status' => 'error',
    'message' => 'Not found',
    'uri' => $uri
]);
