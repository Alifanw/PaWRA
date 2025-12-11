<?php

namespace App\Listeners;

use App\Events\VisitorCheckedIn;
use Illuminate\Support\Facades\DB;

class LogVisitorCheckIn
{
    public function handle(VisitorCheckedIn $event): void
    {
        // Log audit trail for visitor check-in
        DB::table('audit_logs')->insert([
            'user_id' => $event->visit->checked_in_by,
            'action' => 'visitor_checked_in',
            'resource' => 'visits',
            'resource_id' => $event->visit->id,
            'before_json' => null,
            'after_json' => json_encode([
                'visit_id' => $event->visit->id,
                'ticket_sale_id' => $event->visit->ticket_sale_id,
                'visit_token' => $event->visit->visit_token,
                'status' => $event->visit->status,
                'checked_in_at' => $event->visit->checked_in_at,
            ]),
            'ip_addr' => null,
            'user_agent' => null,
            'created_at' => now(),
        ]);
    }
}
