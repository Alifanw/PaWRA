<?php

namespace App\Policies;

use App\Models\User;

class TicketingPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasPermission('view_ticketing') || $user->hasRole('superadmin');
    }

    public function view(User $user)
    {
        return $user->hasPermission('view_ticketing') || $user->hasRole('superadmin');
    }

    public function create(User $user)
    {
        return $user->hasPermission('manage_ticketing') || $user->hasRole('superadmin');
    }

    public function update(User $user)
    {
        return $user->hasPermission('manage_ticketing') || $user->hasRole('superadmin');
    }

    public function delete(User $user)
    {
        return $user->hasPermission('manage_ticketing') || $user->hasRole('superadmin');
    }
}
