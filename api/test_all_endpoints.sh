#!/bin/bash
# API Testing Suite - All Endpoints
# Tests all attendance API endpoints with real data

TOKEN="SECURE_KEY_IGASAR"
BASE_URL="http://localhost/api/absen"
KODE="0319766798"  # Adeli

echo "=========================================="
echo "ATTENDANCE API - COMPLETE TEST SUITE"
echo "=========================================="
echo ""

# Test 1: Today Summary
echo "1. Testing Today's Summary..."
curl -s "${BASE_URL}/today?token=${TOKEN}" | python3 -m json.tool | head -20
echo ""

# Test 2: Check Employee Status
echo "2. Testing Employee Status Check..."
curl -s "${BASE_URL}/check/${KODE}?token=${TOKEN}" | python3 -m json.tool
echo ""

# Test 3: Employee History (7 days)
echo "3. Testing Employee History (7 days)..."
curl -s "${BASE_URL}/employee/${KODE}?token=${TOKEN}&days=7" | python3 -m json.tool | head -25
echo ""

# Test 4: Monthly Statistics
echo "4. Testing Monthly Statistics..."
curl -s "${BASE_URL}/stats?token=${TOKEN}&month=$(date +%Y-%m)" | python3 -m json.tool | head -30
echo ""

# Test 5: Attendance History
echo "5. Testing Attendance History..."
curl -s "${BASE_URL}/history?token=${TOKEN}&start_date=$(date +%Y-%m-01)&kode=${KODE}" | python3 -m json.tool | head -20
echo ""

# Test 6: Export CSV
echo "6. Testing CSV Export..."
OUTPUT_FILE="/tmp/attendance_test_$(date +%Y%m%d).csv"
curl -s "${BASE_URL}/export?token=${TOKEN}&start_date=$(date +%Y-%m-01)" -o "$OUTPUT_FILE"
if [ -f "$OUTPUT_FILE" ]; then
    echo "✅ CSV exported successfully to: $OUTPUT_FILE"
    echo "First 5 lines:"
    head -5 "$OUTPUT_FILE"
else
    echo "❌ CSV export failed"
fi
echo ""

echo "=========================================="
echo "ALL TESTS COMPLETED"
echo "=========================================="
