<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NeighborhoodResource;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NeighborhoodController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => [
            'index', 'show',
        ]]);
    }

    public function index()
    {
        $neighborhoods = NeighborhoodResource::collection(Neighborhood::latest()->get());
        return response()->json(['neighborhoods', $neighborhoods], 200);
    }

    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
        Neighborhood::create([
            'user_id' => $user_id,
            'name' => $request->name,
            'code' => $request->code,
            'status' => $request->status,
        ]);

        return response()->json([
            'status'=>true,
            'Success Message' => 'Neighborhood Created Successfully!'
        ], 200);
    }

    public function show(Neighborhood $neighborhood)
    {   $neighborhood = new NeighborhoodResource($neighborhood);
        return response()->json(['neighborhood' => $neighborhood], 200);
    }

    public function update(Request $request, Neighborhood $neighborhood)
    {
        $user_id = Auth::user()->id;
        $neighborhood->update([
            'auth' => $user_id,
            'name' => $request->name,
            'code' => $request->code,
            'status' => $request->status,
        ]);
        return response()->json(['Success Message' => 'Neighborhood Updated Successfully!'], 200);
    }

    public function destroy(Neighborhood $neighborhood)
    {
        $neighborhood->delete();
        return response()->json(['Success Message' => 'Neighborhood Deleted Successfully!'], 200);

    }
}
