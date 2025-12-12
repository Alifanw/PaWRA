<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope: Get role by slug
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Role has many permissions
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class)->orderBy('created_at');
    }

    /**
     * Get permission strings
     */
    public function getPermissionStrings(): array
    {
        return $this->permissions()
            ->orderBy('created_at')
            ->pluck('permission')
            ->toArray();
    }

    /**
     * Sync permissions for this role
     */
    public function syncPermissions(array $permissions): void
    {
        $permissions = array_filter(
            array_map(fn ($p) => trim(strtolower($p)), $permissions),
            fn ($p) => !empty($p)
        );

        $permissions = array_unique($permissions);

        $existing = $this->permissions()
            ->pluck('permission')
            ->toArray();

        $toRemove = array_diff($existing, $permissions);
        $toAdd = array_diff($permissions, $existing);

        if (!empty($toRemove)) {
            $this->permissions()
                ->whereIn('permission', $toRemove)
                ->delete();
        }

        if (!empty($toAdd)) {
            $insertData = array_map(fn ($p) => [
                'role_id' => $this->id,
                'permission' => $p,
                'created_at' => now(),
            ], $toAdd);

            RolePermission::insert($insertData);
        }
    }
}

