<?php

namespace App\Policies;

use App\Models\ParkingTransaction;
use App\Models\User;

class ParkingTransactionPolicy
{
    /**
     * Determine whether the user can view any parking transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['parking', 'admin', 'superadmin', 'monitoring']);
    }

    /**
     * Determine whether the user can view the parking transaction.
     */
    public function view(User $user, ?ParkingTransaction $parking = null): bool
    {
        return $user->hasRole(['parking', 'admin', 'superadmin', 'monitoring']);
    }

    /**
     * Determine whether the user can create parking transactions.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['parking', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the parking transaction.
     */
    public function update(User $user, ParkingTransaction $parking): bool
    {
        return $user->hasRole(['parking', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the parking transaction.
     */
    public function delete(User $user, ParkingTransaction $parking): bool
    {
        return $user->hasRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can restore the parking transaction.
     */
    public function restore(User $user, ParkingTransaction $parking): bool
    {
        return $user->hasRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the parking transaction.
     */
    public function forceDelete(User $user, ParkingTransaction $parking): bool
    {
        return $user->hasRole(['superadmin']);
    }
}
