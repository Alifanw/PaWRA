<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingUnit extends Model
{
    protected $fillable = [
        'booking_id',
        'product_id',
        'product_code_id',
        'product_availability_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'unit_locked',
        'unit_notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'unit_locked' => 'boolean',
    ];

    /**
     * Get the booking that owns the unit.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the product for this booking unit.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product availability for this booking unit.
     */
    public function availability()
    {
        return $this->belongsTo(ProductAvailability::class, 'product_availability_id');
    }

    /**
     * Get the product code (physical item) for this booking unit.
     */
    public function productCode()
    {
        return $this->belongsTo(ProductCode::class, 'product_code_id');
    }

    /**
     * Calculate subtotal after discount.
     * Subtotal = (unit_price × quantity) - discount_amount
     */
    public function getSubtotalAfterDiscountAttribute(): float
    {
        return max(0, ($this->unit_price * $this->quantity) - $this->discount_amount);
    }

    /**
     * Automatically calculate discount_amount when discount_percentage is set.
     * discount_amount = unit_price × quantity × (discount_percentage / 100)
     */
    public function setDiscountPercentageAttribute($value): void
    {
        $this->attributes['discount_percentage'] = $value;
        
        if ($this->unit_price && $this->quantity) {
            $this->attributes['discount_amount'] = ($this->unit_price * $this->quantity) * ($value / 100);
        }
    }

    /**
     * Get the status of this unit (available, booked, pending, locked)
     */
    public function getStatusAttribute(): string
    {
        if ($this->unit_locked) {
            return 'locked';
        }

        if ($this->booking->status === 'cancelled') {
            return 'cancelled';
        }

        if (in_array($this->booking->status, ['confirmed', 'checked_in', 'checked_out'])) {
            return 'booked';
        }

        return 'pending';
    }

    /**
     * Check if this specific unit is available for modification
     */
    public function isAvailableForModificationAttribute(): bool
    {
        return !$this->unit_locked && $this->booking->status === 'pending';
    }
}
