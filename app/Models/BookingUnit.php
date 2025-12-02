<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingUnit extends Model
{
    protected $fillable = [
        'booking_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'discount_percentage',
        'discount_amount',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
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
}
