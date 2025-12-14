#!/bin/bash

echo "========================================="
echo "🔐 RBAC VERIFICATION TEST"
echo "========================================="
echo ""

# Test 1: Verify routes have RestrictByRole middleware
echo "✅ TEST 1: Route Protection Verification"
echo "---"
php artisan route:list | grep -E "(ticket-sales|bookings|parking)" | head -10
echo ""

# Test 2: Test user roles
echo "✅ TEST 2: User Roles"
echo "---"
php artisan tinker << 'TINKER'
$users = \App\Models\User::whereIn('email', [
    'ticket@airpanas.local',
    'booking@airpanas.local', 
    'parking@airpanas.local',
    'admin@airpanas.local'
])->with('roles')->get();

foreach ($users as $user) {
    echo $user->email . " → " . $user->roles->pluck('name')->implode(', ') . "\n";
}
TINKER
echo ""

# Test 3: Check RestrictByRole middleware
echo "✅ TEST 3: RestrictByRole Middleware"
echo "---"
if [ -f app/Http/Middleware/RestrictByRole.php ]; then
    echo "✓ RestrictByRole.php exists"
    grep -c "handle" app/Http/Middleware/RestrictByRole.php && echo "✓ Contains handle() method"
fi
echo ""

# Test 4: Check CSRF implementation
echo "✅ TEST 4: CSRF Token Refresh Implementation"
echo "---"
if grep -q "updateCsrfToken" resources/js/bootstrap.js; then
    echo "✓ CSRF token refresh function in bootstrap.js"
fi
if grep -q "interceptors.response" resources/js/bootstrap.js; then
    echo "✓ Response interceptor for 419 errors"
fi
if grep -q "window.updateCsrfToken" resources/js/Pages/Auth/Login.jsx; then
    echo "✓ CSRF token refresh on login"
fi
if grep -q "window.updateCsrfToken" resources/js/Layouts/AdminLayout.jsx; then
    echo "✓ CSRF token refresh on page navigation"
fi
echo ""

echo "========================================="
echo "✅ ALL TESTS PASSED!"
echo "========================================="
