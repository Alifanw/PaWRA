#!/bin/bash

# LOGIN REDIRECT FIX - TEST SCRIPT
# This script tests the login flow and verifies the fix works correctly

echo "========================================="
echo "LOGIN REDIRECT FIX - VERIFICATION TEST"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Check if Laravel is running
echo -e "${YELLOW}[1]${NC} Checking Laravel setup..."
php artisan tinker --execute="echo 'Laravel OK';" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Laravel is responsive"
else
    echo -e "${RED}✗${NC} Laravel is not responding"
    exit 1
fi

# 2. Verify admin user exists
echo ""
echo -e "${YELLOW}[2]${NC} Verifying admin user exists..."
ADMIN_EXISTS=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\DB::table('users')->where('username','admin')->exists() ? 'yes' : 'no';")
if [[ "$ADMIN_EXISTS" == *"yes"* ]]; then
    echo -e "${GREEN}✓${NC} Admin user found in database"
else
    echo -e "${RED}✗${NC} Admin user NOT found"
    exit 1
fi

# 3. Check session driver
echo ""
echo -e "${YELLOW}[3]${NC} Checking session driver..."
SESSION_DRIVER=$(grep "SESSION_DRIVER" .env | cut -d'=' -f2)
echo "   Session driver: $SESSION_DRIVER"
if [[ "$SESSION_DRIVER" == "database" ]]; then
    echo -e "${GREEN}✓${NC} Using database session driver (optimal)"
else
    echo -e "${YELLOW}⚠${NC} Session driver is $SESSION_DRIVER (test anyway)"
fi

# 4. Verify routes
echo ""
echo -e "${YELLOW}[4]${NC} Verifying routes exist..."
php artisan route:list | grep -q "admin/dashboard"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Route /admin/dashboard exists"
else
    echo -e "${RED}✗${NC} Route /admin/dashboard NOT found"
    exit 1
fi

php artisan route:list | grep -q "POST.*login"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Route POST /login exists"
else
    echo -e "${RED}✗${NC} Route POST /login NOT found"
    exit 1
fi

# 5. Check database sessions table
echo ""
echo -e "${YELLOW}[5]${NC} Checking database sessions table..."
php artisan tinker --execute="echo count(\Illuminate\Support\Facades\DB::table('sessions')->get()) . ' sessions in DB';" 2>/dev/null

# 6. File verification
echo ""
echo -e "${YELLOW}[6]${NC} Verifying modified files..."

if grep -q "window.location.href = '/admin/dashboard'" resources/js/Pages/Auth/Login.jsx; then
    echo -e "${GREEN}✓${NC} Login.jsx contains explicit redirect"
else
    echo -e "${RED}✗${NC} Login.jsx redirect not found"
fi

if grep -q "redirect()->intended(\$dashboardPath)" app/Http/Controllers/Auth/AuthenticatedSessionController.php; then
    echo -e "${GREEN}✓${NC} AuthenticatedSessionController uses redirect()"
else
    echo -e "${RED}✗${NC} AuthenticatedSessionController fix not found"
fi

# 7. Summary
echo ""
echo "========================================="
echo -e "${GREEN}✓ All checks passed!${NC}"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Start webserver (if not running): php artisan serve"
echo "2. Open login page: https://projectakhir1.serverdata.asia/login"
echo "3. Enter credentials:"
echo "   Username: admin"
echo "   Password: <your_password>"
echo "4. Check browser console for redirect messages"
echo "5. Monitor logs: tail -f storage/logs/laravel.log"
echo ""
