<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category_type',
        'description',
        'status',
    ];

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Scope: Filter by category type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('category_type', $type);
    }

    /**
     * Scope: Get categories for ticket staff (tiket masuk, permainan, kolam)
     */
    public function scopeForTicketStaff($query)
    {
        return $query->where('category_type', 'ticket');
    }

    /**
     * Scope: Get categories for villa staff
     */
    public function scopeForVillaStaff($query)
    {
        return $query->where('category_type', 'villa');
    }

    /**
     * Scope: Get categories for parking staff
     */
    public function scopeForParkingStaff($query)
    {
        return $query->where('category_type', 'parking');
    }
}
