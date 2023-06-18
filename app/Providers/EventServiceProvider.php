<?php

namespace App\Providers;

use App\Models\Constraint;
use App\Models\InterNeighborhoodFare;
use App\Models\TripChange;
use App\Observers\ConstraintObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Observers\InterNeighborhoodFareObserver;
use App\Observers\TripObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        InterNeighborhoodFare::observe(InterNeighborhoodFareObserver::class);
        TripChange::observe(TripObserver::class);
        Constraint::observe(ConstraintObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
