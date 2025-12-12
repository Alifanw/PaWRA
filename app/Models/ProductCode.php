<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCode extends Model
{
    protected $table = 'product_codes';

    protected $fillable = [
        'product_id',
        'code',
        'status',
        'notes',
    ];

    /**
     * Get the product that owns this code.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get booking units for this code
     */
    public function bookingUnits()
    {
        return $this->hasMany(BookingUnit::class, 'product_code_id');
    }

    /**
     * Scope: get only available codes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope: get only unavailable codes
     */
    public function scopeUnavailable($query)
    {
        return $query->where('status', 'unavailable');
    }

    /**
     * Check if code is available for given date range (for booking purposes)
     * @param \Carbon\Carbon $checkin
     * @param \Carbon\Carbon $checkout
     * @return bool
     */
    public function isAvailableForDates(\Carbon\Carbon $checkin, \Carbon\Carbon $checkout): bool
    {
        if ($this->status !== 'available') {
            return false;
        }

        // Check if there's any conflicting booking for this code
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
    }
}
