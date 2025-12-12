<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'code',
        'name',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the prices for the product.
     */
    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Get the booking units for the product.
     */
    public function bookingUnits()
    {
        return $this->hasMany(BookingUnit::class);
    }

    /**
     * Get the ticket sale items for the product.
     */
    public function ticketSaleItems()
    {
        return $this->hasMany(TicketSaleItem::class);
    }

    /**
     * Get the availability units for the product.
     */
    public function availabilityUnits()
    {
        return $this->hasMany(ProductAvailability::class, 'product_id');
    }

    /**
     * Get the product codes (physical items) for this product.
     */
    public function productCodes()
    {
        return $this->hasMany(ProductCode::class, 'product_id');
    }

    /**
     * Get available product codes
     */
    public function availableProductCodes()
    {
        return $this->hasMany(ProductCode::class, 'product_id')->where('status', 'available');
    }

    /**
     * Get count of available product codes
     */
    public function getAvailableCodesCountAttribute(): int
    {
        return $this->availableProductCodes()->count();
    }
}
