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
        'dp_required',
        'dp_percentage',
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
        'dp_percentage' => 'decimal:2',
        'dp_required' => 'boolean',
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
     * Get the effective DP amount for this booking
     * If dp_percentage is set, calculate from total_amount; otherwise use dp_amount
     */
    public function getEffectiveDpAmountAttribute(): float
    {
        if ($this->dp_percentage > 0) {
            return ($this->total_amount * $this->dp_percentage) / 100;
        }
        return $this->dp_amount ?? 0;
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
     * Get the remaining DP amount to be paid (if DP is required)
     */
    public function getRemainingDpAttribute(): float
    {
        if (!$this->dp_required) {
            return 0;
        }
        
        $effectiveDp = $this->effective_dp_amount;
        $dpPaid = $this->bookingPayments()->sum('amount') ?? 0;
        
        return max(0, $effectiveDp - $dpPaid);
    }

    /**
     * Check if DP requirement is satisfied
     */
    public function isDpSatisfiedAttribute(): bool
    {
        if (!$this->dp_required) {
            return true;
        }
        
        $effectiveDp = $this->effective_dp_amount;
        $dpPaid = $this->bookingPayments()->sum('amount') ?? 0;
        
        return $dpPaid >= $effectiveDp;
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
