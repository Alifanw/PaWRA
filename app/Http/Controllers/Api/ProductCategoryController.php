<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of product categories filtered by role
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = ProductCategory::query();

        // Apply role-based filtering
        $allowedCategoryTypes = $this->getAllowedCategoryTypes($user);
        if (!empty($allowedCategoryTypes)) {
            $query->whereIn('category_type', $allowedCategoryTypes);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('category_type')) {
            $query->where('category_type', $request->category_type);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        $categories = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($categories);
    }

    /**
     * Store a newly created product category
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:product_categories,code',
            'name' => 'required|string|max:100',
            'category_type' => 'required|in:ticket,villa,parking,other',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive',
        ]);

        $category = ProductCategory::create($validated);

        $this->logAudit($request, 'category_created', 'product_categories', $category->id, null, $category->toArray());

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category
     */
    public function show(ProductCategory $category): JsonResponse
    {
        $user = auth()->user();
        $allowedCategoryTypes = $this->getAllowedCategoryTypes($user);

        // Check if user has access to this category
        if (!empty($allowedCategoryTypes) && !in_array($category->category_type, $allowedCategoryTypes)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have access to this category'
            ], 403);
        }

        $category->load('products');

        return response()->json(['data' => $category]);
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, ProductCategory $category): JsonResponse
    {
        $user = auth()->user();
        $allowedCategoryTypes = $this->getAllowedCategoryTypes($user);

        // Check if user has access to this category
        if (!empty($allowedCategoryTypes) && !in_array($category->category_type, $allowedCategoryTypes)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have access to this category'
            ], 403);
        }

        $validated = $request->validate([
            'code' => 'sometimes|string|max:30|unique:product_categories,code,' . $category->id,
            'name' => 'sometimes|string|max:100',
            'category_type' => 'sometimes|in:ticket,villa,parking,other',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive',
        ]);

        $before = $category->toArray();
        $category->update($validated);

        $this->logAudit($request, 'category_updated', 'product_categories', $category->id, $before, $category->toArray());

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified category
     */
    public function destroy(Request $request, ProductCategory $category): JsonResponse
    {
        $user = auth()->user();
        $allowedCategoryTypes = $this->getAllowedCategoryTypes($user);

        // Check if user has access to this category
        if (!empty($allowedCategoryTypes) && !in_array($category->category_type, $allowedCategoryTypes)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'You do not have access to this category'
            ], 403);
        }

        // Check if category has products
        if ($category->products()->exists()) {
            return response()->json([
                'error' => 'Cannot delete category with existing products'
            ], 422);
        }

        $before = $category->toArray();
        $category->delete();

        $this->logAudit($request, 'category_deleted', 'product_categories', $category->id, $before, null);

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    /**
     * Get allowed category types for user based on their roles
     */
    protected function getAllowedCategoryTypes($user): array
    {
        if (!$user) {
            return [];
        }

        // Load roles if not already loaded
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        $roleNames = $user->roles->pluck('name')->toArray();
        $allowedTypes = [];

        // Superadmin and admin can see all categories
        if (in_array('superadmin', $roleNames) || in_array('admin', $roleNames)) {
            return ['ticket', 'villa', 'parking', 'other'];
        }

        // Role-specific category access
        if (in_array('ticketing', $roleNames)) {
            $allowedTypes[] = 'ticket';
        }

        if (in_array('booking', $roleNames)) {
            $allowedTypes[] = 'villa';
        }

        if (in_array('parking', $roleNames)) {
            $allowedTypes[] = 'parking';
        }

        // Monitoring and other roles can see all
        if (in_array('monitoring', $roleNames)) {
            $allowedTypes = ['ticket', 'villa', 'parking', 'other'];
        }

        return array_unique($allowedTypes);
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
