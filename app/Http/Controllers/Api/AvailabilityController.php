<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAvailability;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    /**
     * Get availability for a specific product
     * Grouped by villa with list of available rooms
     * 
     * GET /api/availabilities?product_id=1&checkin=2025-12-10&checkout=2025-12-12
     */
    public function getByProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'checkin' => 'required|date_format:Y-m-d',
            'checkout' => 'required|date_format:Y-m-d|after:checkin',
        ]);

        $productId = $request->product_id;
        $checkin = Carbon::createFromFormat('Y-m-d', $request->checkin)->startOfDay();
        $checkout = Carbon::createFromFormat('Y-m-d', $request->checkout)->startOfDay();

        // Get all available units for this product grouped by parent_unit (villa)
        $availabilities = ProductAvailability::forProduct($productId)
            ->available()
            ->get()
            ->filter(function ($availability) use ($checkin, $checkout) {
                // Filter only rooms that are available for the date range
                return $availability->isAvailableForDates($checkin, $checkout);
            })
            ->groupBy('parent_unit')
            ->map(function ($rooms, $parentUnit) use ($checkin, $checkout) {
                return [
                    'parent_unit' => $parentUnit ?: 'All Rooms',
                    'total_rooms' => $rooms->count(),
                    'available_rooms' => $rooms->count(),
                    'rooms' => $rooms->map(function ($room) use ($checkin, $checkout) {
                        return [
                            'id' => $room->id,
                            'unit_name' => $room->unit_name,
                            'unit_code' => $room->unit_code,
                            'description' => $room->description,
                            'max_capacity' => $room->max_capacity,
                            'status' => $room->status,
                        ];
                    })->values()
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $availabilities,
            'checkin' => $checkin->format('Y-m-d'),
            'checkout' => $checkout->format('Y-m-d'),
        ]);
    }

    /**
     * Get all availability for a product (no date filter)
     * 
     * GET /api/availabilities/product/{productId}
     */
    public function getAllByProduct($productId)
    {
        $product = Product::with('availabilityUnits')->findOrFail($productId);

        $availabilities = $product->availabilityUnits()
            ->where('status', 'available')
            ->get()
            ->map(function ($availability) {
                return [
                    'id' => $availability->id,
                    'unit_name' => $availability->unit_name,
                    'unit_code' => $availability->unit_code,
                    'description' => $availability->description,
                    'max_capacity' => $availability->max_capacity,
                    'total_units' => $availability->total_units,
                    'status' => $availability->status,
                ];
            });

        return response()->json([
            'success' => true,
            'product_id' => $productId,
            'product_name' => $product->name,
            'data' => $availabilities,
        ]);
    }

    /**
     * Create new availability unit
     * 
     * POST /api/availabilities
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'unit_name' => 'required|string|max:100',
            'unit_code' => 'required|string|max:50|unique:product_availability',
            'max_capacity' => 'required|integer|min:1',
            'total_units' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'in:available,unavailable,maintenance',
        ]);

        $availability = ProductAvailability::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Availability unit created successfully',
            'data' => $availability,
        ], 201);
    }

    /**
     * Update availability unit
     * 
     * PUT /api/availabilities/{id}
     */
    public function update(Request $request, ProductAvailability $availability)
    {
        $request->validate([
            'unit_name' => 'string|max:100',
            'unit_code' => 'string|max:50|unique:product_availability,unit_code,' . $availability->id,
            'max_capacity' => 'integer|min:1',
            'total_units' => 'integer|min:1',
            'description' => 'nullable|string',
            'status' => 'in:available,unavailable,maintenance',
        ]);

        $availability->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Availability unit updated successfully',
            'data' => $availability,
        ]);
    }

    /**
     * Delete availability unit
     * 
     * DELETE /api/availabilities/{id}
     */
    public function destroy(ProductAvailability $availability)
    {
        $availability->delete();

        return response()->json([
            'success' => true,
            'message' => 'Availability unit deleted successfully',
        ]);
    }

    /**
     * Get availability calendar for date range
     * 
     * GET /api/availabilities/calendar?product_id=1&start_date=2025-12-01&end_date=2025-12-31
     */
    public function getCalendar(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $productId = $request->product_id;
        $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();

        $availabilities = ProductAvailability::forProduct($productId)
            ->available()
            ->get();

        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $calendar[$currentDate->format('Y-m-d')] = $availabilities->map(function ($availability) use ($currentDate) {
                $nextDay = $currentDate->copy()->addDay();
                return [
                    'id' => $availability->id,
                    'unit_name' => $availability->unit_name,
                    'available_count' => $availability->getAvailableCount($currentDate, $nextDay),
                ];
            })->toArray();

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'product_id' => $productId,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'calendar' => $calendar,
        ]);
    }

    /**
     * Get available product codes (physical items) for a product
     * 
     * GET /api/availabilities/product-codes?product_id=1&checkin=2025-12-10&checkout=2025-12-12
     */
    public function getAvailableProductCodes(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'checkin' => 'nullable|date_format:Y-m-d',
            'checkout' => 'nullable|date_format:Y-m-d|after:checkin',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $checkin = $request->checkin ? Carbon::createFromFormat('Y-m-d', $request->checkin)->startOfDay() : null;
        $checkout = $request->checkout ? Carbon::createFromFormat('Y-m-d', $request->checkout)->startOfDay() : null;

        // Get product codes availability
        $query = $product->productCodes()->where('status', 'available');

        if ($checkin && $checkout) {
            // Filter codes without conflicting bookings
            $query->whereDoesntHave('bookingUnits.booking', function ($q) use ($checkin, $checkout) {
                $q->whereNotIn('status', ['cancelled', 'rejected'])
                    ->where(function ($subQ) use ($checkin, $checkout) {
                        $subQ->where('checkin', '<', $checkout)
                            ->where('checkout', '>', $checkin);
                    });
            });
        }

        $availableCodes = $query->get(['id', 'code', 'status', 'notes']);
        $totalAvailable = $availableCodes->count();
        $totalCodes = $product->productCodes()->count();

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'total_codes' => $totalCodes,
            'available_count' => $totalAvailable,
            'unavailable_count' => $totalCodes - $totalAvailable,
            'codes' => $availableCodes->map(fn($code) => [
                'id' => $code->id,
                'code' => $code->code,
                'status' => $code->status,
            ]),
            'checkin' => $checkin?->format('Y-m-d'),
            'checkout' => $checkout?->format('Y-m-d'),
        ]);
    }

    /**
     * Check product availability and get summary
     * 
     * GET /api/availabilities/check?product_id=1&quantity=5&checkin=2025-12-10&checkout=2025-12-12
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'checkin' => 'nullable|date_format:Y-m-d',
            'checkout' => 'nullable|date_format:Y-m-d|after:checkin',
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $checkin = $request->checkin ? Carbon::createFromFormat('Y-m-d', $request->checkin)->startOfDay() : null;
        $checkout = $request->checkout ? Carbon::createFromFormat('Y-m-d', $request->checkout)->startOfDay() : null;
        $requiredQty = (int) $request->quantity;

        // Get available product codes
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

        $availableCount = $query->count();
        $isAvailable = $availableCount >= $requiredQty;

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'available' => $isAvailable,
            'available_count' => $availableCount,
            'required_count' => $requiredQty,
            'message' => $isAvailable 
                ? "Tersedia {$availableCount} unit" 
                : "Hanya tersedia {$availableCount} unit (dibutuhkan {$requiredQty})",
        ]);
    }
}
