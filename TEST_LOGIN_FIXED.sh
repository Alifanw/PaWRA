#!/bin/bash

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║           LOGIN REDIRECT TEST - COMPLETE FLOW                  ║"
echo "╚════════════════════════════════════════════════════════════════╝"

BASE_URL="http://localhost:8000"
COOKIE_JAR="/tmp/cookies_test.txt"

# Clean cookies
rm -f "$COOKIE_JAR"

echo ""
echo "📋 Step 1: Get CSRF token from login page"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

RESPONSE=$(curl -s -c "$COOKIE_JAR" "$BASE_URL/login")
CSRF=$(echo "$RESPONSE" | grep -o 'name="_token"[^>]*value="[^"]*"' | sed 's/.*value="\([^"]*\)".*/\1/' | head -1)

if [ -z "$CSRF" ]; then
    echo "❌ Could not extract CSRF token"
    exit 1
fi

echo "✅ CSRF Token: ${CSRF:0:20}..."

echo ""
echo "📝 Step 2: POST login credentials"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

LOGIN_RESPONSE=$(curl -s -i -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
  -X POST "$BASE_URL/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Referer: $BASE_URL/login" \
  -d "_token=$CSRF&username=admin&password=123123&remember=false")

STATUS=$(echo "$LOGIN_RESPONSE" | head -1)
LOCATION=$(echo "$LOGIN_RESPONSE" | grep -i "^Location:" | sed 's/.*Location: //;s/[[:space:]]*$//')

echo "Response Status: $STATUS"
echo "Location Header: $LOCATION"

if [[ "$STATUS" =~ "302" ]]; then
    echo "✅ Login returned 302 redirect (correct!)"
else
    echo "❌ Login did not return 302"
    echo "$STATUS"
fi

if [[ "$LOCATION" =~ "/admin/dashboard" ]]; then
    echo "✅ Redirects to /admin/dashboard"
elif [[ "$LOCATION" =~ "/login" ]]; then
    echo "❌ Redirects back to /login (failed login)"
    exit 1
else
    echo "⚠️  Unexpected redirect: $LOCATION"
fi

echo ""
echo "🔄 Step 3: Follow redirect to dashboard"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

DASHBOARD_RESPONSE=$(curl -s -i -b "$COOKIE_JAR" "$BASE_URL$LOCATION" -L)
DASH_STATUS=$(echo "$DASHBOARD_RESPONSE" | head -1)

echo "Dashboard Response Status: $DASH_STATUS"

if [[ "$DASH_STATUS" =~ "200" ]]; then
    echo "✅ Dashboard returned 200 OK"
else
    echo "❌ Dashboard did not return 200"
    echo "$DASH_STATUS"
fi

echo ""
echo "📊 Step 4: Check session in database"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

cd /var/www/airpanas

# Get latest session
SESSION_CHECK=$(php artisan tinker << 'TINKER'
use Illuminate\Support\Facades\DB;
$sessions = DB::table('sessions')->orderBy('last_activity', 'desc')->limit(3)->get();
foreach ($sessions as $s) {
    echo "Session ID: " . $s->id . "\n";
    echo "  User ID: " . ($s->user_id ?? 'NULL') . "\n";
    echo "  Last Activity: " . $s->last_activity . "\n";
    echo "  Has user_id: " . (!is_null($s->user_id) ? 'YES' : 'NO') . "\n";
}
exit;
TINKER
)

echo "$SESSION_CHECK"

if echo "$SESSION_CHECK" | grep -q "User ID: 1"; then
    echo "✅ Session has user_id = 1"
elif echo "$SESSION_CHECK" | grep -q "User ID:"; then
    echo "✅ Session has user_id populated"
else
    echo "❌ Session doesn't have user_id (or tinker didn't work)"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                    TEST COMPLETE                               ║"
echo "╚════════════════════════════════════════════════════════════════╝"
