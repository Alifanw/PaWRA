<?php

namespace App\Policies;

use App\Models\TicketSale;
use App\Models\User;

class TicketSalePolicy
{
    /**
     * Determine whether the user can view any ticket sales.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['ticketing', 'admin', 'superadmin', 'monitoring']);
    }

    /**
     * Determine whether the user can view the ticket sale.
     */
    public function view(User $user, ?TicketSale $ticketSale = null): bool
    {
        return $user->hasRole(['ticketing', 'admin', 'superadmin', 'monitoring']);
    }

    /**
     * Determine whether the user can create ticket sales.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['ticketing', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the ticket sale.
     */
    public function update(User $user, TicketSale $ticketSale): bool
    {
        return $user->hasRole(['ticketing', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the ticket sale.
     */
    public function delete(User $user, TicketSale $ticketSale): bool
    {
        return $user->hasRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can restore the ticket sale.
     */
    public function restore(User $user, TicketSale $ticketSale): bool
    {
        return $user->hasRole(['admin', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the ticket sale.
     */
    public function forceDelete(User $user, TicketSale $ticketSale): bool
    {
        return $user->hasRole(['superadmin']);
    }
}
