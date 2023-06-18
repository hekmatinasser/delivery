<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\VehicleConstraint;
use App\Models\VehicleConstraintStatus;

class VehicleConstraintObserver
{
    /**
     * Handle the VehicleConstraint "created" event.
     */
    public function created(VehicleConstraint $vehicleConstraint): void
    {
        VehicleConstraintStatus::create([
            'vehicle_constraint_id' => $vehicleConstraint->id,
            'vehicle_code' => $vehicleConstraint->vehicle_code,
            'user_id' => auth()->user()->id ? auth()->user()->id : 0,
            'constraint_registration_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'quarantined_neighborhood' => $vehicleConstraint->quarantined_neighborhood,
            'description' => 'Vehicle Constraint Created!',
        ]);
    }

    /**
     * Handle the VehicleConstraint "updated" event.
     */
    public function updated(VehicleConstraint $vehicleConstraint): void
    {
        VehicleConstraintStatus::create([
            'vehicle_constraint_id' => $vehicleConstraint->id,
            'vehicle_code' => $vehicleConstraint->vehicle_code,
            'user_id' => auth()->user()->id ? auth()->user()->id : 0,
            'constraint_registration_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'quarantined_neighborhood' => $vehicleConstraint->quarantined_neighborhood,
            'description' => 'Vehicle Constraint Updated!',
        ]);
    }

    /**
     * Handle the VehicleConstraint "deleted" event.
     */
    public function deleted(VehicleConstraint $vehicleConstraint): void
    {
        //
    }

    /**
     * Handle the VehicleConstraint "restored" event.
     */
    public function restored(VehicleConstraint $vehicleConstraint): void
    {
        //
    }

    /**
     * Handle the VehicleConstraint "force deleted" event.
     */
    public function forceDeleted(VehicleConstraint $vehicleConstraint): void
    {
        //
    }
}
