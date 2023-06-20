<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Constraint;
use Illuminate\Support\Arr;
use App\Models\ConstraintStatus;

class ConstraintObserver
{
    /**
     * Handle the Constraint "created" event.
     */
    public function created(Constraint $constraint): void
    {
        ConstraintStatus::create([
            'constraint_id'=>$constraint->id,
            'constraint_code' => $constraint->constraint_code,
            'user_id' => auth()->user()->id ? auth()->user()->id : 0,
            'description' => "The Constraint Created!",
            'changes' => [
                'before' => Arr::except(array_diff($constraint->getOriginal(), $constraint->getAttributes()), 'updated_at'),
                'after' => Arr::except($constraint->getChanges(), 'updated_at'),
            ],
            'constraint_registration_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Handle the Constraint "updated" event.
     */
    public function updated(Constraint $constraint): void
    {
        ConstraintStatus::create([
            'constraint_code' => $constraint->constraint_code,
            'user_id' => auth()->user()->id ? auth()->user()->id : 0,
            'description' => "The Constraint Updated!",
            'changes' => [
                'before' => Arr::except(array_diff($constraint->getOriginal(), $constraint->getAttributes()), 'updated_at'),
                'after' => Arr::except($constraint->getChanges(), 'updated_at'),
            ],
            'constraint_registration_time' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Handle the Constraint "deleted" event.
     */
    public function deleted(Constraint $constraint): void
    {
        //
    }

    /**
     * Handle the Constraint "restored" event.
     */
    public function restored(Constraint $constraint): void
    {
        //
    }

    /**
     * Handle the Constraint "force deleted" event.
     */
    public function forceDeleted(Constraint $constraint): void
    {
        //
    }
}
