#!/bin/bash
# Quick Attendance Test - Test single attendance with doorlock
# Usage: ./quick_test.sh <kode> <status>
# Example: ./quick_test.sh TEST123 masuk

KODE=${1:-TEST123}
STATUS=${2:-masuk}

echo "=========================================="
echo "QUICK ATTENDANCE TEST"
echo "=========================================="
echo "Employee Code: $KODE"
echo "Status: $STATUS"
echo ""

# Call API via PHP
result=$(php -r "
    \$_SERVER['REQUEST_METHOD'] = 'POST';
    \$_SERVER['REQUEST_URI'] = '/api/absen';
    \$_POST = [
        'token' => 'SECURE_KEY_IGASAR',
        'kode' => '$KODE',
        'status' => '$STATUS',
        'device_code' => 'KIOSK-TEST'
    ];
    ob_start();
    require_once '/var/www/airpanas/api/controllers/AbsensiController.php';
    try {
        \$controller = new AbsensiController();
        \$controller->absen();
    } catch (Exception \$e) {
        echo json_encode(['status' => 'error', 'message' => \$e->getMessage()]);
    }
    echo ob_get_clean();
" 2>/dev/null)

# Pretty print JSON
echo "Response:"
echo "$result" | python3 -m json.tool 2>/dev/null || echo "$result"
echo ""

# Check if success
if echo "$result" | grep -q '"status":"success"'; then
    echo "✅ ATTENDANCE RECORDED"
    
    # Check door trigger
    if echo "$result" | grep -q '"door_triggered":true'; then
        echo "🚪 DOOR OPENED"
    fi
else
    echo "❌ FAILED"
fi

echo ""
echo "Latest Doorlock Log:"
tail -1 /var/www/airpanas/api/logs/doorlock.log
echo ""
