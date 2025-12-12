<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCode;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductCodeController extends Controller
{
    /**
     * Get all product codes for a product
     * GET /api/product-codes?product_id=1
     */
    public function index(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $codes = $product->productCodes()
            ->orderBy('code')
            ->get(['id', 'code', 'status', 'notes']);

        $totalCodes = $codes->count();
        $availableCodes = $codes->where('status', 'available')->count();
        $unavailableCodes = $codes->where('status', 'unavailable')->count();
        $maintenanceCodes = $codes->where('status', 'maintenance')->count();

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'stats' => [
                'total' => $totalCodes,
                'available' => $availableCodes,
                'unavailable' => $unavailableCodes,
                'maintenance' => $maintenanceCodes,
            ],
            'codes' => $codes,
        ]);
    }

    /**
     * Get available product codes with optional date range
     * GET /api/product-codes/available?product_id=1&checkin=2025-12-10&checkout=2025-12-12
     */
    public function getAvailable(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'checkin' => 'nullable|date_format:Y-m-d',
            'checkout' => 'nullable|date_format:Y-m-d|after:checkin',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $checkin = $request->checkin ? Carbon::createFromFormat('Y-m-d', $request->checkin)->startOfDay() : null;
        $checkout = $request->checkout ? Carbon::createFromFormat('Y-m-d', $request->checkout)->startOfDay() : null;

        $query = $product->productCodes()->where('status', 'available');

        if ($checkin && $checkout) {
            $query->whereDoesntHave('bookingUnits.booking', function ($q) use ($checkin, $checkout) {
                $q->whereNotIn('status', ['cancelled', 'rejected'])
                    ->where(function ($subQ) use ($checkin, $checkout) {
                        $subQ->where('checkin', '<', $checkout)
                            ->where('checkout', '>', $checkin);
                    });
            });
        }

        $availableCodes = $query->orderBy('code')->get(['id', 'code', 'notes']);

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'available_count' => $availableCodes->count(),
            'codes' => $availableCodes,
            'checkin' => $checkin?->format('Y-m-d'),
            'checkout' => $checkout?->format('Y-m-d'),
        ]);
    }

    /**
     * Create new product code
     * POST /api/product-codes
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'code' => 'required|string|max:50|unique:product_codes,code',
            'status' => 'in:available,unavailable,maintenance',
            'notes' => 'nullable|string',
        ]);

        $productCode = ProductCode::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product code created successfully',
            'data' => $productCode,
        ], 201);
    }

    /**
     * Update product code
     * PUT /api/product-codes/{id}
     */
    public function update(Request $request, ProductCode $productCode)
    {
        $request->validate([
            'code' => 'string|max:50|unique:product_codes,code,' . $productCode->id,
            'status' => 'in:available,unavailable,maintenance',
            'notes' => 'nullable|string',
        ]);

        $productCode->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Product code updated successfully',
            'data' => $productCode,
        ]);
    }

    /**
     * Delete product code
     * DELETE /api/product-codes/{id}
     */
    public function destroy(ProductCode $productCode)
    {
        // Check if code is used in any booking
        if ($productCode->bookingUnits()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete product code that is used in bookings',
            ], 422);
        }

        $productCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product code deleted successfully',
        ]);
    }

    /**
     * Bulk update product code status
     * PUT /api/product-codes/bulk-status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:product_codes,id',
            'status' => 'required|in:available,unavailable,maintenance',
        ]);

        ProductCode::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Product codes status updated successfully',
            'updated_count' => count($request->ids),
        ]);
    }
}
