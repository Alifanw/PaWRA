<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ParkingBooking;

class ParkingBookingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ParkingBooking $booking;

    public function __construct(ParkingBooking $booking)
    {
        $this->booking = $booking;
    }
}
