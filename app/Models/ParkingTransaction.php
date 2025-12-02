<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingTransaction extends Model
{
    protected $fillable = [
        'transaction_code','user_id','vehicle_number','vehicle_type','vehicle_count','total_amount','status','notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
