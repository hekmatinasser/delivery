<?php

namespace App\Observers;

use App\Models\InterNeighborhoodFare;
use App\Models\TheHistoryOfInterNeighborhoodFare;
use Carbon\Carbon;

class InterNeighborhoodFareObserver
{
    /**
     * Handle the InterNeighborhoodFare "created" event.
     */
    public function created(InterNeighborhoodFare $interNeighborhoodFare): void
    {
        TheHistoryOfInterNeighborhoodFare::create([
            'INF_Id'=>$interNeighborhoodFare->id,
            'origin'=>$interNeighborhoodFare->origin,
            'destination'=>$interNeighborhoodFare->destination,
            'fare'=>$interNeighborhoodFare->fare,
            'fare_date'=>Carbon::now()->format('Y-m-d H:i:s'),
            'user_id'=>$interNeighborhoodFare->user_id,
            'description'=>'The Inter Neighbor hood Fare Created!'
        ]);
    }

    /**
     * Handle the InterNeighborhoodFare "updated" event.
     */
    public function updated(InterNeighborhoodFare $interNeighborhoodFare): void
    {
        TheHistoryOfInterNeighborhoodFare::create([
            'INF_Id'=>$interNeighborhoodFare->id,
            'origin'=>$interNeighborhoodFare->origin,
            'destination'=>$interNeighborhoodFare->destination,
            'fare'=>$interNeighborhoodFare->fare,
            'fare_date'=>Carbon::now()->format('Y-m-d H:i:s'),
            'user_id'=>$interNeighborhoodFare->user_id,
            'description'=>'The Inter Neighbor hood Fare Updated!'
        ]);
    }

    /**
     * Handle the InterNeighborhoodFare "deleted" event.
     */
    public function deleted(InterNeighborhoodFare $interNeighborhoodFare): void
    {
        //
    }

    /**
     * Handle the InterNeighborhoodFare "restored" event.
     */
    public function restored(InterNeighborhoodFare $interNeighborhoodFare): void
    {
        //
    }

    /**
     * Handle the InterNeighborhoodFare "force deleted" event.
     */
    public function forceDeleted(InterNeighborhoodFare $interNeighborhoodFare): void
    {
        //
    }
}
