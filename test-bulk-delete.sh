#!/bin/bash

# Bulk Delete Optimization Test Script
# Tests the improved bulk delete functionality

echo "================================"
echo "Bulk Delete Optimization Test"
echo "================================"
echo ""

# Check if running Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run from Laravel root directory."
    exit 1
fi

echo "✓ Running from Laravel directory"
echo ""

# Test 1: Check if route exists
echo "📋 Test 1: Checking bulk-destroy route..."
php artisan route:list | grep "product-codes.*bulk-destroy" && echo "✓ Route exists" || echo "❌ Route not found"
echo ""

# Test 2: Check if controller method exists
echo "📋 Test 2: Checking ProductCodeController::bulkDestroy() method..."
grep -q "public function bulkDestroy" app/Http/Controllers/Admin/ProductCodeController.php && echo "✓ Method exists" || echo "❌ Method not found"
echo ""

# Test 3: Check component updates
echo "📋 Test 3: Checking BulkActionsToolbar component updates..."
grep -q "animate-spin" resources/js/Components/Admin/BulkActionsToolbar.jsx && echo "✓ Animated spinner added" || echo "⚠ Spinner not found"
grep -q "disabled={selectedCount === 0}" resources/js/Components/Admin/BulkActionsToolbar.jsx && echo "✓ Button not disabled during delete" || echo "❌ Button still disabled during delete"
echo ""

# Test 4: Check ProductCodes Index updates
echo "📋 Test 4: Checking ProductCodes Index page updates..."
grep -q "handleBulkDelete" resources/js/Pages/Admin/ProductCodes/Index.jsx && echo "✓ handleBulkDelete function exists" || echo "❌ handleBulkDelete not found"
grep -q "onDeleteSelected={handleBulkDelete}" resources/js/Pages/Admin/ProductCodes/Index.jsx && echo "✓ onDeleteSelected properly configured" || echo "❌ onDeleteSelected not configured"
grep -q "fetch(route('admin.product-codes.bulk-destroy')" resources/js/Pages/Admin/ProductCodes/Index.jsx && echo "✓ Using optimized fetch API" || echo "❌ Not using fetch API"
echo ""

# Test 5: Database check
echo "📋 Test 5: Checking database for product codes..."
php artisan tinker --execute="echo 'Product codes in database: ' . \App\Models\ProductCode::count() . PHP_EOL;" 2>/dev/null || echo "⚠ Could not query database"
echo ""

echo "================================"
echo "Frontend Build Status"
echo "================================"
echo ""

# Test 6: Check build
if [ -f "public/build/manifest.json" ]; then
    echo "✓ Frontend build exists"
    BUILD_TIME=$(ls -l public/build/manifest.json | awk '{print $6" "$7" "$8}')
    echo "  Last build: $BUILD_TIME"
else
    echo "❌ Frontend build not found. Run: npm run build"
fi
echo ""

echo "================================"
echo "Quick Commands"
echo "================================"
echo ""
echo "Build frontend:"
echo "  npm run build"
echo ""
echo "Test bulk delete (manual):"
echo "  1. Go to http://localhost:8000/admin/product-codes"
echo "  2. Select multiple items"
echo "  3. Click 'Delete Selected'"
echo "  4. Confirm deletion"
echo "  5. Observe fast response (1-2 seconds)"
echo ""
echo "View routes:"
echo "  php artisan route:list | grep product-codes"
echo ""
