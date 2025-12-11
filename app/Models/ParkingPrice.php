<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingPrice extends Model
{
    protected $fillable = [
        'vehicle_type',
        'price_per_hour',
        'price_per_day',
        'flat_price',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'flat_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get pricing for a specific vehicle type
     */
    public static function getPrice($vehicleType = 'roda2')
    {
        return self::where('vehicle_type', $vehicleType)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Calculate parking fee based on vehicle type and duration
     * 
     * @param string $vehicleType Vehicle type (roda2, roda4_6)
     * @param int $hours Duration in hours
     * @param bool $useFlatRate If true, use flat_price; if false, calculate by hour
     * @return float Parking fee
     */
    public static function calculateFee($vehicleType = 'roda2', $hours = 1, $useFlatRate = true)
    {
        $pricing = self::getPrice($vehicleType);

        if (!$pricing) {
            return 0;
        }

        if ($useFlatRate && $pricing->flat_price) {
            return (float)$pricing->flat_price;
        }

        // Use hourly rate
        if ($hours >= 24 && $pricing->price_per_day) {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;
            return ($days * $pricing->price_per_day) + ($remainingHours * $pricing->price_per_hour);
        }

        return (float)($hours * $pricing->price_per_hour);
    }
}
