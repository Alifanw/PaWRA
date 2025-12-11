<?php

namespace App\Providers;

use App\Events\VisitorCheckedIn;
use App\Listeners\LogVisitorCheckIn;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\ParkingTransactionCreated::class => [
            \App\Listeners\NotifyMonitoringOnParkingEvent::class,
        ],
        \App\Events\ParkingBookingCreated::class => [
            \App\Listeners\NotifyMonitoringOnParkingEvent::class,
        ],
        VisitorCheckedIn::class => [
            LogVisitorCheckIn::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
