<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

/**
 * Authorization helper for role and permission checks
 */
class AuthHelper
{
    /**
     * Check if authenticated user has a role
     */
    public static function hasRole($role)
    {
        return Auth::check() && Auth::user()->hasRole($role);
    }

    /**
     * Check if authenticated user has a permission
     */
    public static function hasPermission($permission)
    {
        return Auth::check() && Auth::user()->hasPermission($permission);
    }

    /**
     * Check if authenticated user has any of the given permissions
     */
    public static function hasAnyPermission(array $permissions)
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (Auth::user()->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if authenticated user is superadmin
     */
    public static function isSuperAdmin()
    {
        return self::hasRole('superadmin');
    }

    /**
     * Get authenticated user's roles (array of slugs)
     */
    public static function getUserRoles()
    {
        if (!Auth::check()) {
            return [];
        }

        return Auth::user()->roles()->pluck('slug')->toArray();
    }

    /**
     * Get authenticated user's permissions (array of slugs)
     */
    public static function getUserPermissions()
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();
        $permissions = [];

        foreach ($user->roles as $role) {
            foreach ($role->permissions as $perm) {
                $permissions[] = $perm->slug;
            }
        }

        return array_unique($permissions);
    }
}
