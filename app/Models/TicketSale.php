<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSale extends Model
{
    protected $fillable = [
        'invoice_no',
        'sale_date',
        'cashier_id',
        'total_qty',
        'gross_amount',
        'discount_amount',
        'net_amount',
        'status',
        'transaction_status',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'gross_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(\App\Models\TicketSalePayment::class, 'ticket_sale_id');
    }

    /**
     * Get the cashier that made the sale.
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get the items for the ticket sale.
     */
    public function items()
    {
        return $this->hasMany(TicketSaleItem::class);
    }
}
