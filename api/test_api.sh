#!/bin/bash
# Test script for Attendance API
# Tests complete flow: token validation, rate limiting, attendance processing

API_URL="http://localhost/api/absen"
TOKEN="SECURE_KEY_IGASAR"

echo "=========================================="
echo "Attendance API Test Script"
echo "=========================================="
echo ""

# Test 1: Valid masuk
echo "Test 1: Valid masuk (first entry)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"$TOKEN\",\"kode\":\"TEST123\",\"status\":\"masuk\"}" \
  -w "\nHTTP Code: %{http_code}\n\n"
sleep 1

# Test 2: Duplicate masuk (should fail)
echo "Test 2: Duplicate masuk (should fail - already entered)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"$TOKEN\",\"kode\":\"TEST123\",\"status\":\"masuk\"}" \
  -w "\nHTTP Code: %{http_code}\n\n"
sleep 1

# Test 3: Rate limit (should fail if < 10 seconds)
echo "Test 3: Rapid request (should fail - rate limit)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"$TOKEN\",\"kode\":\"TEST123\",\"status\":\"pulang\"}" \
  -w "\nHTTP Code: %{http_code}\n\n"

echo "Waiting 11 seconds to bypass rate limit..."
sleep 11

# Test 4: Valid pulang after waiting
echo "Test 4: Valid pulang (after rate limit)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"$TOKEN\",\"kode\":\"TEST123\",\"status\":\"pulang\"}" \
  -w "\nHTTP Code: %{http_code}\n\n"
sleep 1

# Test 5: Invalid token
echo "Test 5: Invalid token (should return 403)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"WRONG_TOKEN\",\"kode\":\"TEST123\",\"status\":\"masuk\"}" \
  -w "\nHTTP Code: %{http_code}\n\n"
sleep 1

# Test 6: Invalid status
echo "Test 6: Invalid status (should fail)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"$TOKEN\",\"kode\":\"TEST123\",\"status\":\"invalid_status\"}" \
  -w "\nHTTP Code: %{http_code}\n\n"
sleep 1

# Test 7: Missing employee code
echo "Test 7: Missing employee code (should fail)"
curl -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -d "{\"token\":\"$TOKEN\",\"status\":\"masuk\"}" \
  -w "\nHTTP Code: %{http_code}\n\n"
sleep 1

# Test 8: Get attendance history
echo "Test 8: Get attendance history"
curl -X GET "$API_URL/history?token=$TOKEN&start_date=$(date +%Y-%m-%d)" \
  -w "\nHTTP Code: %{http_code}\n\n"

echo "=========================================="
echo "Tests completed. Check logs:"
echo "  - /var/www/airpanas/api/logs/absen.log"
echo "  - /var/www/airpanas/api/logs/doorlock.log"
echo "=========================================="
