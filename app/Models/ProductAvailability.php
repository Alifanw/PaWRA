<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductAvailability extends Model
{
    protected $table = 'product_availability';
    protected $with = [];  // Prevent automatic eager loading

    protected $fillable = [
        'product_id',
        'parent_unit',
        'unit_name',
        'unit_code',
        'max_capacity',
        'status',
        'description',
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'total_units' => 'integer',
    ];

    /**
     * Get the product that owns this availability.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the booking units for this availability.
     */
    public function bookingUnits()
    {
        return $this->hasMany(BookingUnit::class, 'product_availability_id');
    }

    /**
     * Check if availability is available for a date range
     * 
     * @param Carbon $checkin
     * @param Carbon $checkout
     * @return bool
     */
    public function isAvailableForDates(Carbon $checkin, Carbon $checkout): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        // Check if there's any booking conflict
        try {
            $conflictingBookings = $this->bookingUnits()
                ->whereHas('booking', function ($query) use ($checkin, $checkout) {
                    $query->whereNotIn('status', ['cancelled', 'rejected'])
                        ->where(function ($q) use ($checkin, $checkout) {
                            // Booking dates overlap with requested dates
                            $q->where('checkin', '<', $checkout)
                              ->where('checkout', '>', $checkin);
                        });
                })
                ->count();

            return $conflictingBookings === 0;
        } catch (\Exception $e) {
            // If bookings table doesn't exist yet, consider room available
            return true;
        }
    }

    /**
     * Get available count for a specific date range
     * 
     * @param Carbon $checkin
     * @param Carbon $checkout
     * @return int
     */
    public function getAvailableCount(Carbon $checkin, Carbon $checkout): int
    {
        if ($this->status !== 'available') {
            return 0;
        }

        // Count booked units in this date range
        $bookedCount = $this->bookingUnits()
            ->whereHas('booking', function ($query) use ($checkin, $checkout) {
                $query->whereNotIn('status', ['cancelled', 'rejected'])
                    ->where(function ($q) use ($checkin, $checkout) {
                        $q->where('checkin', '<', $checkout)
                          ->where('checkout', '>', $checkin);
                    });
            })
            ->distinct('booking_id')
            ->count();

        return max(0, $this->total_units - $bookedCount);
    }

    /**
     * Scope: Get only available items
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: Get items for specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope: Get items available in date range
     */
    public function scopeAvailableInRange($query, Carbon $checkin, Carbon $checkout)
    {
        return $query->available()
            ->where(function ($q) use ($checkin, $checkout) {
                // Items that don't have conflicting bookings
                $q->whereDoesntHave('bookingUnits.booking', function ($subQuery) use ($checkin, $checkout) {
                    $subQuery->whereNotIn('status', ['cancelled', 'rejected'])
                        ->where('checkin', '<', $checkout)
                        ->where('checkout', '>', $checkin);
                });
            });
    }
}
