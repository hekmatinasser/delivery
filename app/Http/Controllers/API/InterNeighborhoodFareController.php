<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InterNeighborhoodFare;
use Illuminate\Http\Request;

class InterNeighborhoodFareController extends Controller
{
    public function InterNeighborhoodFare(){
        $interNeighborhoodFare = InterNeighborhoodFare::latest()->get();

        return response()->json([
            'status'=>true,
            'interNeighborhoodFare'=>$interNeighborhoodFare,
        ]);
    }

    public function calculatingInterNeighborhoodFare(Request $request)
    {
        $origin = $request->origin;
        $destination = $request->destination;
        if ($this->CheckRepetitiveRecored($origin, $destination)) {
            return response()->json(['message' => 'Record Already exists'], 200);
        }

        $original = $origin . '-' . $destination;
        $reverse = $destination . '-' . $origin;

        InterNeighborhoodFare::create([
            'user_id' => auth()->user()->id,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'original' => $original,
            'reverse' => $reverse,
            'fare' => $request->fare,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Inter Neighborhood Fare Created Successfully',
        ], 200);
    }

    public function editInterNeighborhoodFare(InterNeighborhoodFare $interNeighborhoodFare,Request $request)
    {

        $origin = $request->origin;
        $destination = $request->destination;

        $original = $origin . '-' . $destination;
        $reverse = $destination . '-' . $origin;

        $interNeighborhoodFare->update([
            'user_id' => auth()->user()->id,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'original' => $original,
            'reverse' => $reverse,
            'fare' => $request->fare,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Inter Neighborhood Fare Updated Successfully',
        ], 200);
    }

    protected function CheckRepetitiveRecored(string $origin, string $destination)
    {
        $original = $origin . '-' . $destination;
        $record = InterNeighborhoodFare::orWhere('original', 'LIKE', '%' . $original . '%')
                                        ->orWhere('reverse', 'LIKE', '%' . $original . '%')
                                        ->first();
        if ($record) {
            return true;
        }

    }
}
