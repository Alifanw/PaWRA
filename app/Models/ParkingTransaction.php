<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingTransaction extends Model
{
    protected $fillable = [
        'transaction_code','user_id','vehicle_number','vehicle_type','vehicle_count','total_amount','status','notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'vehicle_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total parking fee based on vehicle count and type
     * Uses flat rate pricing by default
     */
    public function calculateTotalAmount($vehicleType = null, $vehicleCount = null, $useFlatRate = true)
    {
        $type = $vehicleType ?? $this->vehicle_type ?? 'roda2';
        $count = $vehicleCount ?? $this->vehicle_count ?? 1;
        
        $unitPrice = ParkingPrice::calculateFee($type, 1, $useFlatRate);
        return $unitPrice * $count;
    }

    /**
     * Get the effective price per vehicle
     */
    public function getUnitPriceAttribute()
    {
        return ParkingPrice::calculateFee($this->vehicle_type ?? 'roda2', 1, true);
    }
}

