<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * Determine if the user can view any roles
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-roles') || $user->isSuperAdmin();
    }

    /**
     * Determine if the user can view a role
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('view-roles') || $user->isSuperAdmin();
    }

    /**
     * Determine if the user can create roles
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create-roles') || $user->isSuperAdmin();
    }

    /**
     * Determine if the user can update roles
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('update-roles') || $user->isSuperAdmin();
    }

    /**
     * Determine if the user can delete roles
     */
    public function delete(User $user, Role $role): bool
    {
        // Prevent deletion of superadmin role
        if ($role->name === 'superadmin') {
            return false;
        }

        return $user->hasPermission('delete-roles') || $user->isSuperAdmin();
    }

    /**
     * Determine if the user can manage permissions
     */
    public function managePermissions(User $user, Role $role): bool
    {
        return $user->hasPermission('manage-role-permissions') || $user->isSuperAdmin();
    }
}
