#!/bin/bash
# Complete Attendance Flow Test with Doorlock Integration
# Tests: masuk → pulang → lembur → pulang_lembur

echo "=========================================="
echo "COMPLETE ATTENDANCE FLOW TEST"
echo "=========================================="
echo ""

API_URL="http://localhost/api"
TOKEN="SECURE_KEY_IGASAR"
KODE="TEST123"

# Function to test attendance
test_attendance() {
    local status=$1
    local description=$2
    
    echo "Test: $description"
    echo "Status: $status"
    echo -n "Calling API... "
    
    # Use PHP directly to bypass Apache redirect issues
    result=$(php -r "
        \$_SERVER['REQUEST_METHOD'] = 'POST';
        \$_SERVER['REQUEST_URI'] = '/api/absen';
        \$_POST = [
            'token' => '$TOKEN',
            'kode' => '$KODE',
            'status' => '$status',
            'device_code' => 'KIOSK-TEST'
        ];
        ob_start();
        try {
            require_once '/var/www/airpanas/api/controllers/AbsensiController.php';
            \$controller = new AbsensiController();
            \$controller->absen();
        } catch (Exception \$e) {
            echo json_encode(['status' => 'error', 'message' => \$e->getMessage()]);
        }
        echo ob_get_clean();
    " 2>/dev/null)
    
    # Parse response
    success=$(echo "$result" | grep -o '"status":"success"' | wc -l)
    message=$(echo "$result" | grep -o '"message":"[^"]*"' | cut -d'"' -f4)
    door_triggered=$(echo "$result" | grep -o '"door_triggered":[^,}]*' | cut -d':' -f2)
    
    if [ "$success" -eq 1 ]; then
        echo "✅ SUCCESS"
        echo "   Message: $message"
        if [ "$door_triggered" = "true" ]; then
            echo "   🚪 Door triggered!"
        fi
    else
        echo "❌ FAILED"
        echo "   Message: $message"
    fi
    echo ""
}

# Clear previous data
echo "Clearing previous attendance data..."
mysql -u walini_user -p'raHAS1@walini' walini_pj -e "DELETE FROM attendance_logs WHERE employee_id=1;" 2>/dev/null
echo "✅ Cleared"
echo ""

# Test 1: Masuk
test_attendance "masuk" "Clock In (Masuk)"
sleep 11  # Wait for rate limit

# Test 2: Pulang
test_attendance "pulang" "Clock Out (Pulang)"
sleep 11

# Test 3: Lembur
test_attendance "lembur" "Overtime Start (Lembur)"
sleep 11

# Test 4: Pulang Lembur
test_attendance "pulang_lembur" "Overtime End (Pulang Lembur)"
sleep 1

# Show summary
echo "=========================================="
echo "SUMMARY"
echo "=========================================="
echo ""

echo "Attendance Records:"
mysql -u walini_user -p'raHAS1@walini' walini_pj -e "
SELECT 
    DATE_FORMAT(event_time, '%H:%i:%s') as time,
    status,
    device_code
FROM attendance_logs 
WHERE employee_id = 1 
ORDER BY event_time;
" 2>/dev/null

echo ""
echo "Door Events:"
mysql -u walini_user -p'raHAS1@walini' walini_pj -e "
SELECT 
    DATE_FORMAT(event_time, '%H:%i:%s') as time,
    status,
    http_code,
    CASE 
        WHEN http_code = 200 THEN '✅ Success'
        WHEN http_code IS NULL THEN '⏳ Pending'
        ELSE '❌ Failed'
    END as result
FROM door_events 
WHERE employee_code = 'TEST123' 
ORDER BY event_time DESC 
LIMIT 4;
" 2>/dev/null

echo ""
echo "Doorlock Log (last 5 entries):"
echo "----------------------------------------"
tail -5 /var/www/airpanas/api/logs/doorlock.log | while read line; do
    if echo "$line" | grep -q "successfully"; then
        echo "✅ $line"
    elif echo "$line" | grep -q "ERROR"; then
        echo "❌ $line"
    else
        echo "ℹ️  $line"
    fi
done

echo ""
echo "=========================================="
echo "TEST COMPLETED"
echo "=========================================="
