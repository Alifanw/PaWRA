<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Filter active/inactive
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:product_categories,id',
            'code' => 'required|string|max:30|unique:products,code',
            'name' => 'required|string|max:100',
            'base_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        $this->logAudit($request, 'product_created', 'products', $product->id, null, $product->toArray());

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product->load('category')
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'prices']);
        
        return response()->json(['data' => $product]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:product_categories,id',
            'code' => 'sometimes|string|max:30|unique:products,code,' . $product->id,
            'name' => 'sometimes|string|max:100',
            'base_price' => 'sometimes|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $before = $product->toArray();
        $product->update($validated);

        $this->logAudit($request, 'product_updated', 'products', $product->id, $before, $product->toArray());

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product->load('category')
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        // Check if product is used in bookings
        $hasBookings = DB::table('booking_units')->where('product_id', $product->id)->exists();
        
        if ($hasBookings) {
            return response()->json([
                'error' => 'Cannot delete product with existing bookings'
            ], 422);
        }

        $before = $product->toArray();
        $product->delete();

        $this->logAudit($request, 'product_deleted', 'products', $product->id, $before, null);

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Log audit trail
     */
    protected function logAudit(Request $request, string $action, string $resource, $resourceId, $before, $after): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'before_json' => $before ? json_encode($before) : null,
            'after_json' => $after ? json_encode($after) : null,
            'ip_addr' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
