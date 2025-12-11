<?php

namespace App\Events;

use App\Models\Visit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorCheckedIn
{
    use Dispatchable, SerializesModels;

    public Visit $visit;

    public function __construct(Visit $visit)
    {
        $this->visit = $visit;
    }
}
