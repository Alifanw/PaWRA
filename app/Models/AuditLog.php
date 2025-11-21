<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'resource',
        'resource_id',
        'before_json',
        'after_json',
        'ip_addr',
        'user_agent',
    ];

    protected $casts = [
        'before_json' => 'array',
        'after_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
