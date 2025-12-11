<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSalePayment extends Model
{
    protected $table = 'ticket_sale_payments';

    protected $fillable = [
        'ticket_sale_id',
        'method',
        'reference',
        'amount',
        'status',
        'created_by',
        'idempotency_key',
        'reconciled_at',
    ];

    protected $casts = [
        'reconciled_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(TicketSale::class, 'ticket_sale_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a payment is successfully recorded and not refunded
     */
    public function isSuccessful()
    {
        return $this->status === 'successful' && !$this->isRefunded();
    }

    /**
     * Check if this is a refund entry
     */
    public function isRefunded()
    {
        return $this->status === 'refunded';
    }

    /**
     * Get total paid amount (excluding refunds) for a sale
     */
    public static function getTotalPaid($ticketSaleId)
    {
        return static::where('ticket_sale_id', $ticketSaleId)
            ->where('status', 'successful')
            ->sum('amount');
    }

    /**
     * Get total refunded amount for a sale
     */
    public static function getTotalRefunded($ticketSaleId)
    {
        return static::where('ticket_sale_id', $ticketSaleId)
            ->where('status', 'refunded')
            ->sum('amount');
    }

    /**
     * Get balance (amount still owed) for a sale
     */
    public static function getBalance($ticketSaleId, $saleAmount)
    {
        $paid = static::getTotalPaid($ticketSaleId);
        $refunded = static::getTotalRefunded($ticketSaleId);
        return max(0, $saleAmount - $paid + $refunded);
    }
}
