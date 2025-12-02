<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Employee;
use App\Models\Role;
use App\Traits\HasPermissions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    use HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'email',
        'role_id',
        'employee_id',
        'profile_picture',
        'is_block',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_block' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        // legacy single-role support
        return $this->belongsTo(Role::class);
    }

    /**
     * Roles (many-to-many)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * Optional employee mapping
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the bookings created by the user.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    /**
     * Get the ticket sales made by the user.
     */
    public function ticketSales()
    {
        return $this->hasMany(TicketSale::class, 'cashier_id');
    }
}
