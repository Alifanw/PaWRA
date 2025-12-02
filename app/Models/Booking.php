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
        'payment_status',
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
        'payment_status' => 'string',
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

    /**
     * Calculate total amount from booking units (subtotal - discount).
     * Sum all unit subtotals and subtract discounts.
     * 
     * @return float Total price for all units after discounts
     */
    public function getTotalAmountCalculatedAttribute(): float
    {
        return $this->bookingUnits()
            ->sum(\DB::raw('(unit_price * quantity) - discount_amount'))
            ?? 0;
    }

    /**
     * Calculate total amount paid from all booking payments.
     * 
     * @return float Sum of all approved/completed payment amounts
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->bookingPayments()->sum('amount') ?? 0;
    }

    /**
     * Calculate remaining balance to be paid.
     * 
     * @return float Total amount - total paid
     */
    public function getBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->total_paid);
    }

    /**
     * Determine payment status based on payment history.
     * 
     * Logic:
     * - 'unpaid': No payments made (total_paid == 0)
     * - 'partial': Some payments made but balance remains (0 < total_paid < total_amount)
     * - 'paid': Full amount paid (total_paid >= total_amount)
     * 
     * @return string Payment status (unpaid, partial, paid)
     */
    public function getPaymentStatusAttribute(): string
    {
        $totalPaid = $this->total_paid;
        $totalAmount = $this->total_amount;

        if ($totalPaid <= 0) {
            return 'unpaid';
        }

        if ($totalPaid >= $totalAmount) {
            return 'paid';
        }

        return 'partial';
    }

    /**
     * Retrieve outstanding amount to be paid (convenience method).
     * Alias for balance attribute.
     * 
     * @return float Amount remaining to complete payment
     */
    public function getOutstandingAttribute(): float
    {
        return $this->balance;
    }
}
