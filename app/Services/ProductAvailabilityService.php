<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCode;
use App\Models\ProductAvailability;
use Carbon\Carbon;

class ProductAvailabilityService
{
    /**
     * Get available product codes for a product
     * 
     * @param int $productId
     * @param Carbon|null $checkin
     * @param Carbon|null $checkout
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableProductCodes($productId, ?Carbon $checkin = null, ?Carbon $checkout = null)
    {
        $query = ProductCode::where('product_id', $productId)
            ->where('status', 'available');

        if ($checkin && $checkout) {
            // Filter codes that don't have conflicting bookings
            $query->whereDoesntHave('bookingUnits.booking', function ($q) use ($checkin, $checkout) {
                $q->whereNotIn('status', ['cancelled', 'rejected'])
                    ->where(function ($subQ) use ($checkin, $checkout) {
                        $subQ->where('checkin', '<', $checkout)
                            ->where('checkout', '>', $checkin);
                    });
            });
        }

        return $query->get();
    }

    /**
     * Count available product codes
     * 
     * @param int $productId
     * @param Carbon|null $checkin
     * @param Carbon|null $checkout
     * @return int
     */
    public function countAvailableProductCodes($productId, ?Carbon $checkin = null, ?Carbon $checkout = null): int
    {
        return $this->getAvailableProductCodes($productId, $checkin, $checkout)->count();
    }

    /**
     * Check if product has enough available codes
     * 
     * @param int $productId
     * @param int $requiredQuantity
     * @param Carbon|null $checkin
     * @param Carbon|null $checkout
     * @return bool
     */
    public function hasEnoughAvailableCodes($productId, $requiredQuantity, ?Carbon $checkin = null, ?Carbon $checkout = null): bool
    {
        return $this->countAvailableProductCodes($productId, $checkin, $checkout) >= $requiredQuantity;
    }

    /**
     * Get available availability units for a product (villa/room availability)
     * 
     * @param int $productId
     * @param Carbon|null $checkin
     * @param Carbon|null $checkout
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableUnits($productId, ?Carbon $checkin = null, ?Carbon $checkout = null)
    {
        $query = ProductAvailability::where('product_id', $productId)
            ->where('status', 'available');

        if ($checkin && $checkout) {
            $query->whereHas('bookingUnits', function ($q) use ($checkin, $checkout) {
                $q->whereHas('booking', function ($subQ) use ($checkin, $checkout) {
                    $subQ->whereNotIn('status', ['cancelled', 'rejected'])
                        ->where(function ($subSubQ) use ($checkin, $checkout) {
                            $subSubQ->where('checkin', '<', $checkout)
                                ->where('checkout', '>', $checkin);
                        });
                });
            });
        }

        return $query->get();
    }

    /**
     * Check availability status for a product
     * Returns array with detailed information
     * 
     * @param int $productId
     * @param int $requiredQuantity
     * @param Carbon|null $checkin
     * @param Carbon|null $checkout
     * @return array
     */
    public function checkAvailability($productId, $requiredQuantity = 1, ?Carbon $checkin = null, ?Carbon $checkout = null): array
    {
        $product = Product::find($productId);

        if (!$product) {
            return [
                'available' => false,
                'message' => 'Product not found',
                'available_count' => 0,
                'required_count' => $requiredQuantity,
            ];
        }

        $availableCount = $this->countAvailableProductCodes($productId, $checkin, $checkout);
        $isAvailable = $availableCount >= $requiredQuantity;

        return [
            'available' => $isAvailable,
            'message' => $isAvailable 
                ? "Tersedia {$availableCount} unit" 
                : "Hanya tersedia {$availableCount} unit (dibutuhkan {$requiredQuantity})",
            'available_count' => $availableCount,
            'required_count' => $requiredQuantity,
        ];
    }

    /**
     * Allocate product codes for a booking
     * 
     * @param int $productId
     * @param int $quantity
     * @param Carbon|null $checkin
     * @param Carbon|null $checkout
     * @return array Array of ProductCode ids
     */
    public function allocateProductCodes($productId, $quantity, ?Carbon $checkin = null, ?Carbon $checkout = null): array
    {
        $availableCodes = $this->getAvailableProductCodes($productId, $checkin, $checkout);

        if ($availableCodes->count() < $quantity) {
            throw new \Exception("Not enough available product codes. Required: {$quantity}, Available: {$availableCodes->count()}");
        }

        return $availableCodes->take($quantity)->pluck('id')->toArray();
    }
}
