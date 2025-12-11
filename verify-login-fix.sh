#!/bin/bash

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}LOGIN REDIRECT FIX - FINAL VERIFICATION${NC}"
echo -e "${BLUE}========================================${NC}\n"

TOTAL_TESTS=0
PASSED_TESTS=0

test_item() {
    local test_name=$1
    local result=$2
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    if [ $result -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $test_name"
        PASSED_TESTS=$((PASSED_TESTS + 1))
    else
        echo -e "${RED}✗${NC} $test_name"
    fi
}

# Test 1: Laravel responsive
echo -e "${YELLOW}[1] Testing Laravel Setup...${NC}"
php artisan tinker --execute="echo 'Laravel OK';" > /dev/null 2>&1
test_item "Laravel responsive" $?

# Test 2: Check users table
echo -e "\n${YELLOW}[2] Testing Database Schema...${NC}"
USERS_COLS=$(mysql -u walini_user -p'raHAS1@walini' walini_pj -e "DESCRIBE users;" 2>/dev/null | grep -c "full_name\|role_id\|is_active")
test_item "Users table has full_name, role_id, is_active (expects 3)" $((3 - USERS_COLS))

# Test 3: Admin user data
echo -e "\n${YELLOW}[3] Testing Admin User Data...${NC}"
ADMIN_DATA=$(mysql -u walini_user -p'raHAS1@walini' walini_pj -e "SELECT full_name, role_id, is_active FROM users WHERE username='admin';" 2>/dev/null | grep -i "administrator" | grep -c "1")
test_item "Admin user has full_name and role_id and is_active" $((ADMIN_DATA - 1))

# Test 4: Routes exist
echo -e "\n${YELLOW}[4] Testing Routes...${NC}"
php artisan route:list 2>/dev/null | grep -q "admin/dashboard" 
test_item "Route /admin/dashboard exists" $?

php artisan route:list 2>/dev/null | grep -q "POST.*login"
test_item "Route POST /login exists" $?

# Test 5: Frontend files
echo -e "\n${YELLOW}[5] Testing Frontend Build...${NC}"
[ -f "public/build/manifest.json" ] && test_item "Build manifest exists" 0 || test_item "Build manifest exists" 1
[ -f "public/build/assets/app-"*.js ] && test_item "App JS built" 0 || test_item "App JS built" 1
grep -q "window.location.href = '/admin/dashboard'" resources/js/Pages/Auth/Login.jsx
test_item "Login.jsx has redirect logic" $?

# Test 6: Controllers & Middleware
echo -e "\n${YELLOW}[6] Testing Controller & Middleware...${NC}"
grep -q "redirect()->intended" app/Http/Controllers/Auth/AuthenticatedSessionController.php
test_item "AuthenticatedSessionController uses redirect()" $?

grep -q "HandleAuthResponse" app/Http/Kernel.php
test_item "HandleAuthResponse middleware configured" $?

# Test 7: User Model
echo -e "\n${YELLOW}[7] Testing User Model...${NC}"
grep -q "full_name" app/Models/User.php
test_item "User model includes full_name" $?

grep -q "role_id" app/Models/User.php
test_item "User model includes role_id" $?

# Summary
echo -e "\n${BLUE}========================================${NC}"
echo -e "Test Results: ${GREEN}${PASSED_TESTS}${NC}/${TOTAL_TESTS} passed"
if [ $PASSED_TESTS -eq $TOTAL_TESTS ]; then
    echo -e "${GREEN}✓ ALL TESTS PASSED - Ready for testing!${NC}"
    echo -e "${BLUE}========================================${NC}"
    echo -e "\n${YELLOW}Next Steps:${NC}"
    echo "1. Start webserver: ${BLUE}php artisan serve${NC}"
    echo "2. Open in browser: ${BLUE}http://localhost:8000/login${NC}"
    echo "3. Login with: ${BLUE}admin / 123123${NC}"
    echo "4. Expected: Redirect to dashboard in ~500ms"
    echo "5. Check browser console (F12) for debug messages"
    exit 0
else
    echo -e "${RED}✗ Some tests failed - Review issues above${NC}"
    echo -e "${BLUE}========================================${NC}"
    exit 1
fi
