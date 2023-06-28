<?php

namespace App\Observers;

use App\Models\Trip;
use App\Models\TripChange;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class TripObserver
{
    /**
     * Handle the Trip "created" event.
     */
    public function created(Trip $trip): void
    {
        TripChange::create([
            'trip_id' => $trip->id,
            'user_id' => auth()->user()->id,
            'trip_code' => $trip->trip_code,
            'description' => "The Trip Created!",

            'changes' => json_encode([
                'before' => Arr::except(array_diff($trip->getOriginal(), $trip->getAttributes()), 'updated_at'),
                'after' => Arr::except($trip->getChanges(), 'updated_at'),
            ]),
            'status_determining_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Handle the Trip "updated" event.
     */
    public function updated(Trip $trip): void
    {
        TripChange::create([
            'trip_id' => $trip->id,
            'user_id' => auth()->user()->id,
            'trip_code' => $trip->trip_code,
            'description' => "The Trip Status changed!",
            'changes' => json_encode([
                'before' => Arr::except(array_diff($trip->getOriginal(), $trip->getAttributes()), 'updated_at'),
                'after' => Arr::except($trip->getChanges(), 'updated_at'),
            ]),
            'status_determining_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Handle the Trip "deleted" event.
     */
    public function deleted(Trip $trip): void
    {
        //
    }

    /**
     * Handle the Trip "restored" event.
     */
    public function restored(Trip $trip): void
    {
        //
    }

    /**
     * Handle the Trip "force deleted" event.
     */
    public function forceDeleted(Trip $trip): void
    {
        //
    }
}
