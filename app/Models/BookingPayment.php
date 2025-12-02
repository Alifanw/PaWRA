<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    // Table uses only `created_at` (no `updated_at`) so disable automatic timestamps
    public $timestamps = false;
    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'payment_reference',
        'paid_at',
        'cashier_id',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
