<?php

namespace App\Policies;

use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermission('view_attendance') || $user->hasRole('superadmin');
    }

    public function view(User $user)
    {
        return $user->hasPermission('view_attendance') || $user->hasRole('superadmin');
    }

    public function create(User $user)
    {
        return $user->hasPermission('manage_attendance') || $user->hasRole('superadmin');
    }

    public function update(User $user)
    {
        return $user->hasPermission('manage_attendance') || $user->hasRole('superadmin');
    }

    public function delete(User $user)
    {
        return $user->hasPermission('manage_attendance') || $user->hasRole('superadmin');
    }
}
