<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\ParkingAlertNotification;

class NotifyMonitoringOnParkingEvent implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {
        // Build a light payload depending on event type
        $payload = [];
        if (isset($event->transaction)) {
            $t = $event->transaction;
            $payload = [
                'type' => 'transaction',
                'id' => $t->id,
                'code' => $t->transaction_code,
                'amount' => (float) $t->total_amount,
                'status' => $t->status,
                'time' => $t->created_at->toDateTimeString(),
            ];
        } elseif (isset($event->booking)) {
            $b = $event->booking;
            $payload = [
                'type' => 'booking',
                'id' => $b->id,
                'code' => $b->booking_code,
                'lot' => $b->parking_lot,
                'start_time' => $b->start_time,
                'status' => $b->status,
                'time' => $b->created_at->toDateTimeString(),
            ];
        } else {
            return;
        }

        // Send notification to all users with role 'petugas_monitoring'
        $userIds = \DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('roles.slug', 'petugas_monitoring')
            ->pluck('user_id')
            ->all();

        $users = \App\Models\User::whereIn('id', $userIds)->get();

        if ($users->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($users, new ParkingAlertNotification($payload));
        }
    }
}
