<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\InterNeighborhoodFareController;
use App\Http\Requests\Admin\CreateNeighborhoodRequest;
use App\Http\Requests\Admin\UpdateNeighborhoodRequest;
use App\Http\Resources\NeighborhoodResource;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\InterNeighborhoodFare;
use App\Models\TheHistoryOfInterNeighborhoodFare;

class NeighborhoodController extends BaseController
{

    /**
     *
     * @OA\Get(
     *     path="/api/v1/neighborhood",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     summary="Get a list of neighborhoods",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="The number of stores to return per page (default 10).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to return (default 1).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="origin_neighborhood_id",
     *         in="query",
     *         description="Calculate fee from origin neighborhood (default 1).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function index(Request $request)
    {
        // $neighborhoods = NeighborhoodResource::collection(Neighborhood::latest()->get());

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $origin = $request->input('origin_neighborhood_id', null);
        $res = Neighborhood::with([
            'user' => function ($query) {
                $query->select('id', 'name', 'family');
            }
        ])
            ->with([
                'fee' => function ($query) use ($origin) {
                    $query->where('destination', '=', $origin);
                }
            ])
            ->with([
                'feeBack' => function ($query) use ($origin) {
                    $query->where('origin', '=', $origin);
                }
            ])
            ->paginate($perPage, ['*'], 'page', $page);

        // $res['data'] = NeighborhoodResource::collection(($res['data']));
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     *
     * @OA\Get(
     *     path="/api/v1/neighborhood/fee/histories",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     summary="Get a list of neighborhoods fee histories",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="The number of stores to return per page (default 10).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to return (default 1).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function histories(Request $request)
    {
        // $neighborhoods = NeighborhoodResource::collection(Neighborhood::latest()->get());

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $res = TheHistoryOfInterNeighborhoodFare::with(['inf', 'user'])->paginate($perPage, ['*'], 'page', $page);

        // $res['data'] = NeighborhoodResource::collection(($res['data']));
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }
    /**
     *
     * @OA\Get(
     *     path="/api/v1/neighborhood/fee",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     summary="Get a list of neighborhoods fee",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="The number of stores to return per page (default 10).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number to return (default 1).",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function fees(Request $request)
    {
        // $neighborhoods = NeighborhoodResource::collection(Neighborhood::latest()->get());

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $origin = $request->input('origin_neighborhood_id', null);
        $res = InterNeighborhoodFare::with(['origin', 'destination'])->paginate($perPage, ['*'], 'page', $page);

        // $res['data'] = NeighborhoodResource::collection(($res['data']));
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/neighborhood",
     *     summary="Create a new neighborhood",
     *     security={ {"sanctum": {} }},
     *     tags={"Neighborhood"},
     *     @OA\RequestBody(
     *         description="Neighborhood object",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateNeighborhoodRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Neighborhood created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/NeighborhoodResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function store(CreateNeighborhoodRequest $request)
    {
        $user_id = Auth::user()->id;
        $code = $request->get('code', (new TripController())->generateUniqueCode(10, 'code-'));

        if ($request->hasFile('image')) {
            $path = uploadPublicImageToS3($request->file('image'), 'public/');
        } else
            $path = '';

        $response = Neighborhood::create([
            'user_id' => $user_id,
            'name' => $request->name,
            'code' => $code,
            'image' => $path,
            'status' => $request->status,
        ]);

        $neighborhoods = Neighborhood::select('id')->get();

        $origin = $response->id;
        foreach ($neighborhoods as $neighborhood) {
            $destination = $neighborhood->id;


            if (!(new InterNeighborhoodFareController())->CheckRepetitiveRecored($origin, $destination)) {


                $original = $origin . '-' . $destination;
                $reverse = $destination . '-' . $origin;

                InterNeighborhoodFare::create([
                    'user_id' => auth()->user()->id,
                    'origin' => $origin,
                    'destination' => $destination,
                    'original' => $original,
                    'reverse' => $reverse,
                    'fare' => 0,
                ]);
            }
        }


        return $this->sendResponse($response, 'Neighborhood Created Successfully!');
    }

    public function show(Neighborhood $neighborhood)
    {
        $neighborhood = new NeighborhoodResource($neighborhood);
        return $this->sendResponse([$neighborhood], Lang::get('http-statuses.200'));
    }

    /**
     * @OA\POST(
     *     path="/api/v1/admin/neighborhood/{neighborhood_id}",
     *     summary="Update a neighborhood",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="neighborhood_id",
     *         in="path",
     *         description="ID of the neighborhood to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     tags={"Neighborhood"},
     *     @OA\RequestBody(
     *         description="Neighborhood object",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateNeighborhoodRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Neighborhood created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/NeighborhoodResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Neighborhood not found",
     *     )
     * )
     */
    public function update(UpdateNeighborhoodRequest $request, $neighborhoodId)
    {
        $neighborhood = Neighborhood::findOrFail($neighborhoodId);
        $request->validate([
            'name' => 'unique:neighborhoods,name,' . $neighborhood->id
        ]);

        $path = $neighborhood->image;
        if ($request->hasFile('image')) {
            $path = uploadPublicImageToS3($request->file('image'), 'public/');
        } else {
            $path = '';
        }

        $response = $neighborhood->update([
            'name' => $request->name,
            'status' => $request->status,
            'image' => $path,
        ]);
        return $this->sendResponse($response, 'Neighborhood Updated Successfully!');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/neighborhood/{neighborhood_id}",
     *     summary="Delete a neighborhood",
     *     tags={"Neighborhood"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="neighborhood_id",
     *         in="path",
     *         description="ID of the neighborhood to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Neighborhood created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/NeighborhoodResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Neighborhood not found",
     *     )
     * )
     */
    public function destroy($neighborhoodId)
    {
        $neighborhood = Neighborhood::findOrFail($neighborhoodId);
        $neighborhood->delete();
        return $this->sendResponse('', 'Neighborhood Deleted Successfully!');
    }
}