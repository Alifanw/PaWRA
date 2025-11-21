<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketSaleItem extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'ticket_sale_id',
        'product_id',
        'qty',
        'unit_price',
        'discount_amount',
        'line_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function ticketSale()
    {
        return $this->belongsTo(TicketSale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
