<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingMonitoring extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id','action','status','meta','created_at'
    ];

    protected $dates = ['created_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
