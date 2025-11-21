<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'customer_name',
        'customer_phone',
        'checkin',
        'checkout',
        'night_count',
        'room_count',
        'status',
        'dp_amount',
        'total_amount',
        'discount_amount',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'checkin' => 'datetime',
        'checkout' => 'datetime',
        'dp_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    /**
     * Get the user that created the booking.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the booking.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the booking units for the booking.
     */
    public function bookingUnits()
    {
        return $this->hasMany(BookingUnit::class);
    }

    /**
     * Get the payments for the booking.
     */
    public function bookingPayments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    /**
     * Alias for bookingPayments
     */
    public function payments()
    {
        return $this->hasMany(BookingPayment::class);
    }
}
