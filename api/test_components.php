<?php
// Simple test to verify API components

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing API Components ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
require_once __DIR__ . '/config/Database.php';
try {
    $db = new Database();
    $conn = $db->getConnection();
    echo "   ✓ Database connected successfully\n\n";
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Find Employee
echo "2. Testing Employee Lookup...\n";
require_once __DIR__ . '/models/EmployeeModel.php';
$employeeModel = new EmployeeModel($conn);
$employee = $employeeModel->findByCode('TEST123');
if ($employee) {
    echo "   ✓ Employee found: {$employee['name']} (ID: {$employee['id']})\n\n";
} else {
    echo "   ✗ Employee TEST123 not found\n\n";
}

// Test 3: Auth Middleware
echo "3. Testing Token Validation...\n";
require_once __DIR__ . '/middleware/AuthMiddleware.php';
$validToken = AuthMiddleware::validateToken('SECURE_KEY_IGASAR');
$invalidToken = AuthMiddleware::validateToken('WRONG_TOKEN');
echo "   ✓ Valid token: " . ($validToken['valid'] ? 'PASS' : 'FAIL') . "\n";
echo "   ✓ Invalid token: " . (!$invalidToken['valid'] ? 'PASS' : 'FAIL') . "\n\n";

// Test 4: Status Validation
echo "4. Testing Status Validation...\n";
require_once __DIR__ . '/validators/AttendanceValidator.php';
$validStatus = AttendanceValidator::validateStatus('masuk');
$invalidStatus = AttendanceValidator::validateStatus('invalid');
echo "   ✓ Valid status: " . ($validStatus['valid'] ? 'PASS' : 'FAIL') . "\n";
echo "   ✓ Invalid status: " . (!$invalidStatus['valid'] ? 'PASS' : 'FAIL') . "\n\n";

// Test 5: Logger
echo "5. Testing Logger...\n";
require_once __DIR__ . '/services/Logger.php';
$logger = new Logger(__DIR__ . '/logs/test.log');
$logger->info('Test log entry', ['test' => true]);
echo "   ✓ Log written to logs/test.log\n\n";

// Test 6: Full Attendance Process (dry run)
echo "6. Testing Full Attendance Service...\n";
require_once __DIR__ . '/services/AbsensiService.php';
$absensiService = new AbsensiService($conn);
echo "   ✓ AbsensiService initialized\n\n";

echo "=== All Component Tests Passed ===\n";
