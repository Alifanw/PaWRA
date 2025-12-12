<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductCodeController extends Controller
{
    /**
     * Show product code management page
     */
    public function index(Request $request)
    {
        $query = ProductCode::with('product');

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%")
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        $productCodes = $query->orderBy('code')
            ->paginate(20)
            ->through(fn ($code) => [
                'id' => $code->id,
                'code' => $code->code,
                'product_id' => $code->product_id,
                'product_name' => $code->product->name,
                'status' => $code->status,
                'notes' => $code->notes,
                'created_at' => $code->created_at,
            ]);

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Admin/ProductCodes/Index', [
            'productCodes' => $productCodes,
            'products' => $products,
            'filters' => $request->only(['product_id', 'status', 'search']),
        ]);
    }

    /**
     * Create new product code
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'code' => 'required|string|max:50|unique:product_codes,code',
            'status' => 'in:available,unavailable,maintenance',
            'notes' => 'nullable|string|max:500',
        ]);

        ProductCode::create($validated);

        return back()->with('success', 'Product code created successfully');
    }

    /**
     * Update product code
     */
    public function update(Request $request, ProductCode $productCode)
    {
        $validated = $request->validate([
            'code' => 'string|max:50|unique:product_codes,code,' . $productCode->id,
            'status' => 'in:available,unavailable,maintenance',
            'notes' => 'nullable|string|max:500',
        ]);

        $productCode->update($validated);

        return back()->with('success', 'Product code updated successfully');
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:product_codes,id',
            'status' => 'required|in:available,unavailable,maintenance',
        ]);

        ProductCode::whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        return back()->with('success', count($validated['ids']) . ' product codes updated successfully');
    }

    /**
     * Delete product code
     */
    public function destroy(ProductCode $productCode)
    {
        // Check if code is used in any booking
        if ($productCode->bookingUnits()->exists()) {
            return back()->with('error', 'Cannot delete product code that is used in bookings');
        }

        $productCode->delete();

        return back()->with('success', 'Product code deleted successfully');
    }

    /**
     * Bulk delete product codes
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:product_codes,id',
        ]);

        // Check which codes are used in bookings
        $codesInUse = ProductCode::whereIn('id', $validated['ids'])
            ->whereHas('bookingUnits')
            ->pluck('code')
            ->toArray();

        if (!empty($codesInUse)) {
            return back()->with('error', 'Cannot delete codes used in bookings: ' . implode(', ', $codesInUse));
        }

        // Bulk delete
        $deletedCount = ProductCode::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', "$deletedCount product code(s) deleted successfully");
    }
}
