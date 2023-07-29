<?php

namespace App\Providers;

use App\Models\Constraint;
use App\Models\InterNeighborhoodFare;
use App\Models\Trip;
use App\Models\TripChange;
use App\Models\User;
use App\Models\VehicleConstraint;
use App\Observers\ConstraintObserver;
use App\Observers\userObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Observers\InterNeighborhoodFareObserver;
use App\Observers\TripObserver;
use App\Observers\VehicleConstraintObserver;
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
        Constraint::observe(ConstraintObserver::class);
        VehicleConstraint::observe(VehicleConstraintObserver::class);
        Trip::observe(TripObserver::class);
        User::observe(userObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
