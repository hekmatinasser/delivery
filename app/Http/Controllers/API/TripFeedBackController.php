<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripFeedBack;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class TripFeedBackController extends BaseController
{

    /**
     *
     * @OA\Get(
     *     path="/api/v1/admin/trip/feedbacks",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     summary="Get a list of trip feedbacks",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="The number of trips to return per page (default 10).",
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
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *         ),)
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $res = TripFeedBack::with(['user', 'trip'])->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/trip/{code}/feedbacks",
     *     summary="Get trip feedback",
     *     description="Get the feedback for a specific trip",
     *     security={ {"sanctum": {} }},
     *     tags={"Trip"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The trip code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of feedback per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
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
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *         ),),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     )
     * )
     */
    public function get(Request $request, $code)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $trip = Trip::where('trip_code', '=', $code)->firstOrFail();

        $res = TripFeedBack::with(['user'])
            ->where('trip_id', '=', $trip->id)->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/trip/{code}/feedbacks",
     *     summary="Create trip feedback",
     *     description="Create feedback for a specific trip",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The trip code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="rating", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     )
     * )
     */
    public function create(Request $request, $code)
    {
        $userId = $request->get('user_id', Auth::id());

        $trip = Trip::where('trip_code', '=', $code)->firstOrFail();

        TripFeedBack::create(
            [
                'trip_id' => $trip->id,
                'user_id' => $userId,
                'rating' => $request->rating,
                'description' => $request->description,
            ]
        );

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/trip/{code}/feedbacks/{id}",
     *     summary="Update trip feedback",
     *     description="Update feedback for a specific trip",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The trip code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The feedback ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="rating", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip or feedback not found"
     *     )
     * )
     */
    public function update(Request $request, $code, $id)
    {
        $userId = $request->get('user_id', Auth::id());

        $trip = Trip::where('trip_code', '=', $code)->firstOrFail();

        $tripFeedback = TripFeedBack::where('id', '=', $id)->firstOrFail();
        $tripFeedback->trip_id = $trip->id;
        $tripFeedback->user_id = $userId;
        $tripFeedback->rating =  $request->rating;
        $tripFeedback->description =  $request->description;
        $tripFeedback->save();

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/store/trip/{code}/feedbacks",
     *     summary="Create trip feedback",
     *     description="Create feedback for a specific trip",
     *     tags={"Store"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The trip code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="rating", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     )
     * )
     */
    public function createWithStore(Request $request, $code)
    {
        $user = User::find(Auth::id());
        $user->load('store');
        if (!$user->store) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $trip = Trip::where('trip_code', '=', $code)->where('store_id', $user->store->Id)->firstOrFail();

        TripFeedBack::create(
            [
                'trip_id' => $trip->id,
                'user_id' => Auth::id(),
                'rating' => $request->rating,
                'description' => $request->description,
            ]
        );

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/store/trip/{code}/feedbacks/{id}",
     *     summary="Update trip feedback",
     *     description="Update feedback for a specific trip",
     *     tags={"Store"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The trip code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The feedback ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="rating", type="integer"),
     *             @OA\Property(property="description", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Trip or feedback not found"
     *     )
     * )
     */

    public function updateWithStore(Request $request, $code, $id)
    {
        $user = User::find(Auth::id());
        $user->load('store');
        if (!$user->store) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $trip = Trip::where('trip_code', '=', $code)->where('store_id', $user->store->id)->firstOrFail();

        $tripFeedback = TripFeedBack::where('id', '=', $id)->where('user_id', $user->id)->firstOrFail();
        $tripFeedback->trip_id = $trip->id;
        $tripFeedback->user_id = Auth::id();
        $tripFeedback->rating =  $request->rating;
        $tripFeedback->description =  $request->description;
        $tripFeedback->save();

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

        /**
     * @OA\Get(
     *     path="/api/v1/store/trip/{code}/feedbacks",
     *     summary="Get trip feedback",
     *     description="Get the feedback for a specific trip",
     *     security={ {"sanctum": {} }},
     *     tags={"Store"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The trip code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of feedback per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
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
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *         ),),
     *     @OA\Response(
     *         response=404,
     *         description="Trip not found"
     *     )
     * )
     */
    public function getWithStore(Request $request, $code)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $user = User::find(Auth::id());
        $user->load('store');
        if (!$user->store) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $trip = Trip::where('trip_code', '=', $code)->where('store_id', $user->store->id)->firstOrFail();

        $res = TripFeedBack::with(['user'])
            ->where('trip_id', '=', $trip->id)->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

}
