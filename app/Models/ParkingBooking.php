<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingBooking extends Model
{
    protected $fillable = [
        'booking_code','user_id','customer_name','vehicle_number','parking_lot','start_time','end_time','status','price'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
