#!/bin/bash

# Role-Based Access Control Manual Testing Script
# This script tests login and access control for different roles

echo "=========================================="
echo "AirPanas Role-Based Access Control Testing"
echo "=========================================="
echo ""

# Get application URL from .env or use default
APP_URL=${APP_URL:-"http://localhost"}
if grep -q "^APP_URL=" .env; then
    APP_URL=$(grep "^APP_URL=" .env | cut -d '=' -f 2)
fi

echo "Application URL: $APP_URL"
echo ""

# Function to test login
test_login() {
    local email=$1
    local password=$2
    local role=$3
    
    echo "═══════════════════════════════════════"
    echo "Testing LOGIN: $role ($email)"
    echo "═══════════════════════════════════════"
    
    # Get CSRF token from login page
    CSRF_TOKEN=$(curl -s -b /tmp/cookies_${role}.txt -c /tmp/cookies_${role}.txt \
        "$APP_URL/login" | grep -oP 'name="_token" value="\K[^"]+' | head -1)
    
    if [ -z "$CSRF_TOKEN" ]; then
        echo "❌ Failed to get CSRF token"
        return 1
    fi
    
    echo "✅ Got CSRF token"
    
    # Try login
    RESPONSE=$(curl -s -b /tmp/cookies_${role}.txt -c /tmp/cookies_${role}.txt \
        -X POST "$APP_URL/login" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "_token=$CSRF_TOKEN&email=$email&password=$password" \
        -L -w "\n%{http_code}")
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    BODY=$(echo "$RESPONSE" | head -n-1)
    
    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
        echo "✅ Login successful (HTTP $HTTP_CODE)"
        return 0
    else
        echo "❌ Login failed (HTTP $HTTP_CODE)"
        echo "Response: $BODY" | head -5
        return 1
    fi
}

# Function to test access
test_access() {
    local role=$1
    local url=$2
    local description=$3
    local should_allow=$4
    
    echo ""
    echo "Testing access: $description"
    
    RESPONSE=$(curl -s -b /tmp/cookies_${role}.txt \
        "$APP_URL$url" \
        -L -w "\n%{http_code}\n%{url_effective}")
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -2 | head -1)
    FINAL_URL=$(echo "$RESPONSE" | tail -1)
    
    if [ "$should_allow" = "yes" ]; then
        if [ "$HTTP_CODE" = "200" ]; then
            echo "  ✅ Access ALLOWED (HTTP $HTTP_CODE)"
            return 0
        else
            echo "  ❌ Expected ALLOWED but got HTTP $HTTP_CODE"
            echo "  Final URL: $FINAL_URL"
            return 1
        fi
    else
        if [[ "$FINAL_URL" != "$APP_URL$url"* ]]; then
            echo "  ✅ Access BLOCKED with redirect (HTTP $HTTP_CODE)"
            echo "  Redirected to: $FINAL_URL"
            return 0
        else
            echo "  ❌ Expected BLOCKED but got access (HTTP $HTTP_CODE)"
            return 1
        fi
    fi
}

echo ""
echo "Step 1: Testing TICKETING User"
echo "─────────────────────────────"

if test_login "ticket@airpanas.local" "123123" "ticketing"; then
    test_access "ticketing" "/admin/ticket-sales" "Access POS Tiket" "yes"
    test_access "ticketing" "/admin/bookings" "Access Booking (should redirect)" "no"
    test_access "ticketing" "/admin/products" "Access Products (should redirect)" "no"
    test_access "ticketing" "/admin/dashboard" "Access Dashboard" "yes"
fi

echo ""
echo "Step 2: Testing BOOKING User"
echo "─────────────────────────────"

if test_login "booking@airpanas.local" "123123" "booking"; then
    test_access "booking" "/admin/bookings" "Access Booking" "yes"
    test_access "booking" "/admin/ticket-sales" "Access POS Tiket (should redirect)" "no"
    test_access "booking" "/admin/parking" "Access Parking (should redirect)" "no"
    test_access "booking" "/admin/dashboard" "Access Dashboard" "yes"
fi

echo ""
echo "Step 3: Testing PARKING User"
echo "─────────────────────────────"

if test_login "parking@airpanas.local" "123123" "parking"; then
    test_access "parking" "/admin/parking" "Access Parking" "yes"
    test_access "parking" "/admin/ticket-sales" "Access POS Tiket (should redirect)" "no"
    test_access "parking" "/admin/bookings" "Access Booking (should redirect)" "no"
    test_access "parking" "/admin/dashboard" "Access Dashboard" "yes"
fi

echo ""
echo "=========================================="
echo "Testing Complete!"
echo "=========================================="

# Cleanup
rm -f /tmp/cookies_*.txt
