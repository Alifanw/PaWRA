<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAvailability;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductCodeController extends Controller
{
    /**
     * Show product availability management page
     */
    public function index(Request $request)
    {
        $query = ProductAvailability::with('product');

        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && $request->category) {
            // product.category holds the category_type; products table does not have category_type column
            $query->whereHas('product.category', function ($q) use ($request) {
                $q->where('category_type', $request->category);
            });
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('unit_name', 'like', "%{$search}%")
                  ->orWhere('unit_code', 'like', "%{$search}%")
                  ->orWhere('parent_unit', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $availabilities = $query->orderBy('parent_unit')
            ->orderBy('unit_name')
            ->paginate(25)
            ->through(fn ($avail) => [
                'id' => $avail->id,
                'product_id' => $avail->product_id,
                'product_name' => $avail->product->name,
                'product_code' => $avail->product->code,
                'parent_unit' => $avail->parent_unit,
                'unit_name' => $avail->unit_name,
                'unit_code' => $avail->unit_code,
                'max_capacity' => $avail->max_capacity,
                'description' => $avail->description,
                'status' => $avail->status,
                'category_type' => $avail->product->category?->category_type ?? null,
                'created_at' => $avail->created_at,
            ]);

        // Load products with their category relation and expose category_type from the category
        $products = Product::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'category_id'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'category_type' => $p->category?->category_type ?? null,
                ];
            });

        $categories = [
            'villa' => 'Villa',
            'ticket' => 'Tiket & Kolam',
            'parking' => 'Parking',
        ];

        return Inertia::render('Admin/ProductCodes/Index', [
            'availabilities' => $availabilities,
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['product_id', 'status', 'category', 'search']),
        ]);
    }

    /**
     * Create new availability unit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'parent_unit' => 'required|string|max:100',
            'unit_name' => 'required|string|max:100',
            'unit_code' => 'required|string|max:50|unique:product_availability,unit_code',
            'max_capacity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'status' => 'in:available,unavailable,maintenance',
        ]);

        $validated['status'] = $validated['status'] ?? 'available';
        ProductAvailability::create($validated);

        return back()->with('success', 'Unit tersedia berhasil ditambahkan');
    }

    /**
     * Update availability unit
     */
    public function update(Request $request, ProductAvailability $productCode)
    {
        $validated = $request->validate([
            'parent_unit' => 'required|string|max:100',
            'unit_name' => 'required|string|max:100',
            'unit_code' => 'required|string|max:50|unique:product_availability,unit_code,' . $productCode->id,
            'max_capacity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'status' => 'in:available,unavailable,maintenance',
        ]);

        $productCode->update($validated);

        return back()->with('success', 'Unit tersedia berhasil diupdate');
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:product_availability,id',
            'status' => 'required|in:available,unavailable,maintenance',
        ]);

        ProductAvailability::whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        return back()->with('success', count($validated['ids']) . ' unit berhasil diupdate');
    }

    /**
     * Delete availability unit
     */
    public function destroy(ProductAvailability $productCode)
    {
        // Check if used in bookings
        if ($productCode->bookingUnits()->exists()) {
            return response()->json([
                'error' => 'Tidak bisa hapus unit yang sudah digunakan dalam booking'
            ], 422);
        }

        $productCode->delete();

        return back()->with('success', 'Unit tersedia berhasil dihapus');
    }

    /**
     * Bulk delete availability units
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:product_availability,id',
        ]);

        // Check which are used in bookings
        $unitsInUse = ProductAvailability::whereIn('id', $validated['ids'])
            ->whereHas('bookingUnits')
            ->pluck('unit_code')
            ->toArray();

        if (!empty($unitsInUse)) {
            return response()->json([
                'error' => 'Tidak bisa hapus unit yang digunakan dalam booking: ' . implode(', ', $unitsInUse)
            ], 422);
        }

        $deletedCount = ProductAvailability::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => "$deletedCount unit berhasil dihapus",
            'deleted_count' => $deletedCount
        ]);
    }
}
