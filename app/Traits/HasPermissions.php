<?php

namespace App\Traits;

use App\Models\Permission;

trait HasPermissions
{
    /**
     * Check if user has a role (by slug/name)
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            // check many-to-many
            if ($this->relationLoaded('roles')) {
                foreach ($this->roles as $r) {
                    if (in_array($role, [$r->slug ?? null, $r->name ?? null])) return true;
                }
            } else {
                if ($this->roles()->where('slug', $role)->orWhere('name', $role)->exists()) return true;
            }

            // legacy single role
            if ($this->role && ($this->role->slug === $role || $this->role->name === $role)) return true;
            return false;
        }

        if ($role instanceof \App\Models\Role) {
            return $this->roles()->where('id', $role->id)->exists() || ($this->role && $this->role->id === $role->id);
        }

        return false;
    }

    /**
     * Check if user has a permission (by slug)
     */
    public function hasPermission($permissionSlug)
    {
        // Eager-loaded optimization
        if ($this->relationLoaded('roles')) {
            foreach ($this->roles as $role) {
                if ($role->relationLoaded('permissions')) {
                    foreach ($role->permissions as $p) {
                        if ($p->slug === $permissionSlug) return true;
                    }
                } else {
                    if ($role->permissions()->where('slug', $permissionSlug)->exists()) return true;
                }
            }
        } else {
            // query roles -> permissions
            $count = \DB::table('permission_role')
                ->join('roles', 'permission_role.role_id', '=', 'roles.id')
                ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
                ->join('role_user', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $this->id)
                ->where('permissions.slug', $permissionSlug)
                ->count();

            if ($count) return true;
        }

        // check legacy role_permissions table (text-based)
        if ($this->role) {
            foreach ($this->role->rolePermissions as $rp) {
                if (isset($rp->permission) && $rp->permission === $permissionSlug) return true;
            }
        }

        return false;
    }
}
