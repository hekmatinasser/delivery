<?php

namespace App\Observers;

use App\Models\user;
use Illuminate\Support\Facades\Auth;

class userObserver
{
    /**
     * Handle the user "created" event.
     */
    public function created(user $user): void
    {
        $user->created_by = Auth::id();
        $user->save();
    }

    /**
     * Handle the user "updated" event.
     */
    public function updated(user $user): void
    {
        //
    }

    /**
     * Handle the user "deleted" event.
     */
    public function deleted(user $user): void
    {
        //
    }

    /**
     * Handle the user "restored" event.
     */
    public function restored(user $user): void
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     */
    public function forceDeleted(user $user): void
    {
        //
    }
}
