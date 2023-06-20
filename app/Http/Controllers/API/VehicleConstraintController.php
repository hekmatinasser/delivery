<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\VehicleConstraint;
use App\Http\Controllers\Controller;

class VehicleConstraintController extends Controller
{
    public function applyVehicleConstraint(Request $request){


        VehicleConstraint::updateOrCreate(
        [
            'vehicle_code'=> $request->vehicle_code
        ],
        [
            'user_id'=> auth()->user()->id,
            'constraint_registration_time'=> Carbon::now()->format('Y-m-d H:i:s'),
            'quarantined_neighborhood'=> $request->quarantined_neighborhood,
        ]);

        return response()->json([
            'status'=>true,
            'message'=>'Vehicle Constraint Created Successfully!'
        ]);
    }
}
