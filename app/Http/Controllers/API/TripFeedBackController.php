<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TripFeedBack;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TripFeedBackController extends Controller
{



    public function index()
    {
        $tripFeedBacks = TripFeedBack::latest()->get();

        return response()->json([
            'status'=>true,
            'tripFeedBacks'=> $tripFeedBacks,
        ]);
    }

    public function updateOrCreate(Request $request)
    {
        TripFeedBack::updateOrCreate(
            [
                'trip_code' => $request->trip_code,
                'vehicle_user_code' => $request->vehicle_user_code,
            ],
            [
                'shop_user_code' => $request->shop_user_code,
                'vehicle_feedback_time' => $request->has('vehicle_feedback_time') ? Carbon::now()->format('Y-m-d H:i:s') : null,
                'vehicle_rating' => $request->vehicle_rating,
                'vehicle_description' => $request->vehicle_description,
                'shop_feedback_time' => $request->has('vehicle_feedback_time') ? Carbon::now()->format('Y-m-d H:i:s') : null,
                'shop_rating' => $request->shop_rating,
                'shop_description' => $request->shop_description,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Feedbak Saved successfully!',
        ], 200);
    }
}
