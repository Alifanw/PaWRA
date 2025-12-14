<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductPageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Product::with('category');

        // Filter by user role
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        if (in_array('ticketing', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            // Ticketing role - only ticket products
            $query->whereHas('category', fn($q) => $q->where('category_type', 'ticket'));
        } elseif (in_array('booking', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            // Booking role - only villa products
            $query->whereHas('category', fn($q) => $q->where('category_type', 'villa'));
        } elseif (in_array('parking', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            // Parking role - only parking products
            $query->whereHas('category', fn($q) => $q->where('category_type', 'parking'));
        }
        // Admin and superadmin can see all products

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')
            ->paginate(15)
            ->through(fn ($product) => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category_name' => $product->category?->name ?? '-',
                'base_price' => $product->base_price,
                'is_active' => $product->is_active,
            ]);

        // Filter categories based on user role for the dropdown
        $categoriesQuery = ProductCategory::orderBy('name');
        
        if (in_array('ticketing', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            $categoriesQuery->where('category_type', 'ticket');
        } elseif (in_array('booking', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            $categoriesQuery->where('category_type', 'villa');
        } elseif (in_array('parking', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles)) {
            $categoriesQuery->where('category_type', 'parking');
        }
        
        $categories = $categoriesQuery->get(['id', 'name']);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'code' => 'required|string|max:30|unique:products,code',
            'name' => 'required|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        Product::create(array_merge($validated, [
            'is_active' => $validated['is_active'] ?? true
        ]));

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'code' => 'required|string|max:30|unique:products,code,' . $product->id,
            'name' => 'required|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        // Check if product is used in bookings or ticket sales
        if ($product->bookingUnits()->exists() || $product->ticketSaleItems()->exists()) {
            return response()->json([
                'error' => 'Cannot delete product that is used in bookings or ticket sales'
            ], 422);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
        ]);

        // Check which products are used in bookings or ticket sales
        $productsInUse = Product::whereIn('id', $validated['ids'])
            ->where(function($query) {
                $query->whereHas('bookingUnits')
                      ->orWhereHas('ticketSaleItems');
            })
            ->pluck('name')
            ->toArray();

        if (!empty($productsInUse)) {
            return response()->json([
                'error' => 'Cannot delete products in use: ' . implode(', ', $productsInUse)
            ], 422);
        }

        // Bulk delete
        $deletedCount = Product::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => "$deletedCount product(s) deleted successfully",
            'deleted_count' => $deletedCount
        ]);
    }
}