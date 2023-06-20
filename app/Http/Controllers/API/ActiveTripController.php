<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActiveTrip;
use Illuminate\Http\Request;

class ActiveTripController extends Controller
{
    public function index()
    {
        $activeTrips = ActiveTrip::latest()->get();
        return response()->json(
            [
                'status' => true,
                'activeTrips' => $activeTrips,
            ], 200
        );
    }

    public function updateOrCreate(Request $request)
    {
        ActiveTrip::updateOrCreate(
            [
                'trip_code' => $request->trip_code,
            ],
            [
                'status_code' => $request->status_code,
            ]);

        return response()->json(
            [
                'status' => true,
                'message' => 'Active Trips Status changed successfully',
            ], 200
        );
    }

}
