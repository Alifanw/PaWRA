<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['booking', 'admin', 'superadmin', 'monitoring']);
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, ?Booking $booking = null): bool
    {
        return $user->hasRole(['booking', 'admin', 'superadmin', 'monitoring']);
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['booking', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->hasRole(['booking', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->hasRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can restore the booking.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return $user->hasRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the booking.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return $user->hasRole(['superadmin']);
    }
}
