<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users for the role.
     */
    public function users()
    {
        // keep legacy relation
        return $this->hasMany(User::class);
    }

    /**
     * Get the permissions for the role.
     */
    // many-to-many permissions via pivot `permission_role`
    public function permissions()
    {
        // new normalized permissions
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id')->withTimestamps();
    }

    // legacy role_permissions table support
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}
