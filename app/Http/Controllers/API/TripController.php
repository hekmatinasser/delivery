<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TripChangesResource;
use App\Models\Trip;
use App\Models\TripChange;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function tripGetChanges($trip_id)
    {
        $tripChanges = TripChangesResource::collection(TripChange::where('trip_id', $trip_id)->latest()->get());
        return $tripChanges;
    }

    public function tripUpdateOrCreate(Request $request)
    {
        Trip::updateOrCreate(
            [
                'trip_code' => $request->trip_code,
            ],
            [
                'shop_code' => $request->shop_code,
                'vehicle_code' => $request->motor_code,
                'destination' => $request->destination,
                'request_registration_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'shipment_prepare_time' => Carbon::now()->addMinutes($request->shipment_prepare_time)->format('Y-m-dH:i:s'),
                'trip_rial_fare' => $request->trip_rial_fare,
                'status' => $request->status,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'description' => $request->description,
                'manager_description' => $request->manager_description,

            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Changes Successfully Stored!',
        ]);
    }
}
