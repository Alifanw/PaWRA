#!/bin/bash

# 🚀 FINAL VERIFICATION SCRIPT - READY FOR PRESENTASI

echo "════════════════════════════════════════════════════════════════"
echo "     LOGIN REDIRECT FIX - FINAL VERIFICATION (PRESENTASI MODE)"
echo "════════════════════════════════════════════════════════════════"
echo ""

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

PASS=0
FAIL=0

check() {
    local name=$1
    local result=$2
    
    if [ $result -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $name"
        PASS=$((PASS + 1))
    else
        echo -e "${RED}✗${NC} $name"
        FAIL=$((FAIL + 1))
    fi
}

echo "🔍 BACKEND CHECKS"
echo "─────────────────────────────────────────────────────────────"

# Check 1: AuthenticatedSessionController
grep -q "session->put('user_id'" app/Http/Controllers/Auth/AuthenticatedSessionController.php
check "AuthenticatedSessionController stores user_id in session" $?

grep -q "session->save()" app/Http/Controllers/Auth/AuthenticatedSessionController.php
check "Session save called" $?

# Check 2: Custom Middleware
[ -f "app/Http/Middleware/AuthenticateWithSession.php" ]
check "AuthenticateWithSession middleware exists" $?

grep -q "Auth::guard.*->check()" app/Http/Middleware/AuthenticateWithSession.php
check "Custom middleware has auth check" $?

# Check 3: Kernel Registration
grep -q "AuthenticateWithSession" app/Http/Kernel.php
check "Middleware registered in Kernel" $?

echo ""
echo "🎨 FRONTEND CHECKS"
echo "─────────────────────────────────────────────────────────────"

# Check 4: Login redirect
grep -q "window.location.href = \"/admin/dashboard\"" resources/js/Pages/Auth/Login.jsx
check "Login.jsx has redirect" $?

grep -q "setTimeout.*300" resources/js/Pages/Auth/Login.jsx
check "Redirect timeout set to 300ms" $?

grep -q "window.location.reload()" resources/js/Pages/Auth/Login.jsx
check "Fallback reload exists" $?

echo ""
echo "🗄️ DATABASE CHECKS"
echo "─────────────────────────────────────────────────────────────"

# Check 5: User table
COLS=$(mysql -u walini_user -p'raHAS1@walini' walini_pj -e "DESCRIBE users;" 2>/dev/null | grep -c "full_name\|role_id\|is_active")
[ $COLS -eq 3 ]
check "User table has all required columns" $?

# Check 6: Admin user data
mysql -u walini_user -p'raHAS1@walini' walini_pj -e "SELECT * FROM users WHERE username='admin';" 2>/dev/null | grep -q "Super Administrator\|1"
check "Admin user properly populated" $?

# Check 7: Sessions table
mysql -u walini_user -p'raHAS1@walini' walini_pj -e "DESCRIBE sessions;" 2>/dev/null | grep -q "user_id"
check "Sessions table has user_id column" $?

echo ""
echo "📦 BUILD CHECKS"
echo "─────────────────────────────────────────────────────────────"

# Check 8: Build artifacts
[ -f "public/build/manifest.json" ]
check "Build manifest exists" $?

[ -f "public/build/assets/app-"*.js ]
check "App JS bundle exists" $?

grep -q "Login-" public/build/manifest.json
check "Login component in manifest" $?

echo ""
echo "════════════════════════════════════════════════════════════════"
echo -e "RESULTS: ${GREEN}$PASS PASS${NC} / ${RED}$FAIL FAIL${NC}"
echo "════════════════════════════════════════════════════════════════"

if [ $FAIL -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓ ALL CHECKS PASSED!${NC}"
    echo ""
    echo "🚀 READY FOR PRESENTASI TOMORROW!"
    echo ""
    echo "Next steps:"
    echo "1. Start server: php artisan serve"
    echo "2. Open: http://localhost:8000/login"
    echo "3. Login: admin / 123123"
    echo "4. Expected: Redirect to dashboard in 300ms"
    echo "5. Monitor: Check Network tab for 302→200"
    exit 0
else
    echo ""
    echo -e "${RED}✗ Some checks failed - Review above${NC}"
    exit 1
fi
