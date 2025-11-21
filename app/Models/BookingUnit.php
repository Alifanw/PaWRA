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
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
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
}
