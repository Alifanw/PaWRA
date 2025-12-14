#!/bin/bash

# Color codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                  ROLE PERMISSION VERIFICATION SCRIPT                       ║${NC}"
echo -e "${BLUE}║                                                                            ║${NC}"
echo -e "${BLUE}║  This script verifies that the RBAC system is correctly configured         ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════════════════════╝${NC}"
echo ""

cd /var/www/airpanas

echo -e "${YELLOW}1. Checking Roles in Database...${NC}"
php artisan tinker --execute="
\$roles = \App\Models\Role::orderBy('id')->pluck('name')->toArray();
echo \"   Found \" . count(\$roles) . \" roles:\n\";
foreach (\$roles as \$role) {
  echo \"   ✓ \$role\n\";
}
" 2>/dev/null || echo "   Error checking roles"

echo ""
echo -e "${YELLOW}2. Checking Test Users...${NC}"
php artisan tinker --execute="
\$users = \App\Models\User::orderBy('id')->get();
echo \"   Found \" . count(\$users) . \" users:\n\";
foreach (\$users as \$user) {
  \$roles = \$user->roles->pluck('name')->toArray();
  \$roleStr = !empty(\$roles) ? implode(', ', \$roles) : 'No role assigned';
  echo \"   ✓ {\$user->email} -> {\$roleStr}\n\";
}
" 2>/dev/null || echo "   Error checking users"

echo ""
echo -e "${YELLOW}3. Checking Middleware Registration...${NC}"
if grep -q "'role' => \\\\App\\\\Http\\\\Middleware\\\\RestrictByRole::class" app/Http/Kernel.php; then
  echo -e "   ${GREEN}✓${NC} RestrictByRole middleware registered in Kernel.php"
else
  echo "   ✗ RestrictByRole middleware NOT found in Kernel.php"
fi

echo ""
echo -e "${YELLOW}4. Checking Routes Configuration...${NC}"
if grep -q "RestrictByRole::class" routes/web.php; then
  echo -e "   ${GREEN}✓${NC} RestrictByRole middleware used in web routes"
  echo "   Route groups checked:"
  grep -c "RestrictByRole::class" routes/web.php | xargs echo "   Found" instances
else
  echo "   ✗ RestrictByRole middleware NOT found in web routes"
fi

echo ""
echo -e "${YELLOW}5. Checking User Model...${NC}"
if grep -q "protected \\\$with = \['roles'\]" app/Models/User.php; then
  echo -e "   ${GREEN}✓${NC} User model auto-loads roles"
else
  echo "   ✗ User model NOT auto-loading roles"
fi

echo ""
echo -e "${YELLOW}6. Checking Frontend Build...${NC}"
if [ -d "public/build" ] && [ "$(ls -A public/build)" ]; then
  echo -e "   ${GREEN}✓${NC} Frontend assets built successfully"
  echo "   Build contains $(find public/build -type f | wc -l) files"
else
  echo "   ✗ Frontend assets NOT built"
fi

echo ""
echo -e "${BLUE}════════════════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ VERIFICATION COMPLETE${NC}"
echo ""
echo -e "${YELLOW}TEST CREDENTIALS:${NC}"
echo -e "  Superadmin: superadmin@airpanas.local / Admin123!"
echo -e "  Admin:      admin@airpanas.local / 123123"
echo -e "  Ticketing:  ticket@airpanas.local / 123123"
echo -e "  Booking:    booking@airpanas.local / 123123"
echo -e "  Parking:    parking@airpanas.local / 123123"
echo -e "  Monitoring: monitor@airpanas.local / 123123"
echo ""
echo -e "${YELLOW}NEXT STEP:${NC} Visit http://projectakhir1.serverdata.asia/admin/dashboard"
echo ""
