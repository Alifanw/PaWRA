<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ParkingTransaction;

class ParkingTransactionCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ParkingTransaction $transaction;

    public function __construct(ParkingTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
