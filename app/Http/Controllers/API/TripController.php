<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTripRequest;
use App\Http\Requests\Admin\UpdateTripRequest;
use App\Http\Resources\TripChangesResource;
use App\Models\Trip;
use App\Models\TripChange;
use App\Models\Vehicle;
use Carbon\Carbon;
use Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class TripController extends BaseController
{
    public function tripGetChanges($trip_id)
    {
        $tripChanges = TripChangesResource::collection(TripChange::where('trip_id', $trip_id)->latest()->get());
        return $tripChanges;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/trip",
     *     summary="Create a new trip",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreTripRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Changes Successfully Stored!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"role": "مقدار نقش کاربر اشتباه است"}),
     *             @OA\Property(property="message", type="string", example="مقدار نقش کاربر اشتباه است"),
     *             @OA\Property(property="code", type="integer", example=400)
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}}),
     *             @OA\Property(property="code", type="integer", example=422),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *         ),
     * ))
     */
    public function create(StoreTripRequest $request)
    {
        $trip =
            Trip::create(
                [
                    'trip_code' => $this->generateUniqueCode(),
                    'store_id' => $request->store_id,
                    'vehicle_id' => $request->vehicle_id,
                    'origin' => $request->origin_id,
                    'destination' => $request->destination_id,
                    'request_registration_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'shipment_prepare_time' => Carbon::parse($request->shipment_prepare_time)->format('Y-m-d H:i:s'),
                    'deliver_time' => strlen($request->deliver_time) ? Carbon::parse($request->deliver_time)->format('Y-m-d H:i:s') : null,
                    'arrive_time' => strlen($request->arrive_time) ? Carbon::parse($request->arrive_time)->format('Y-m-d H:i:s') : null,
                    'trip_rial_fare' => $request->trip_rial_fare,
                    'status' => $request->status,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'description' => $request->description,
                    'manager_description' => $request->manager_description,

                ]
            );


        return $this->sendResponse($trip, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/trip/{code}",
     *     summary="Update a new trip",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateTripRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Changes Successfully Updated!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object", example={"role": "مقدار نقش کاربر اشتباه است"}),
     *             @OA\Property(property="message", type="string", example="مقدار نقش کاربر اشتباه است"),
     *             @OA\Property(property="code", type="integer", example=400)
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={"email": {"The email field is required."}}),
     *             @OA\Property(property="code", type="integer", example=422),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *         ),
     * ))
     */
    public function update(UpdateTripRequest $request, $code)
    {
        $trip = Trip::where('trip_code', '=', $code)->firstOrFail();
        $trip =
            Trip::updateOrCreate(
                ['id' => $trip->id],
                [
                    'store_id' => $request->store_id,
                    'vehicle_id' => $request->vehicle_id,
                    'origin' => $request->origin_id,
                    'destination' => $request->destination_id,
                    'shipment_prepare_time' => Carbon::parse($request->shipment_prepare_time)->format('Y-m-d H:i:s'),
                    'deliver_time' => strlen($request->deliver_time) ? Carbon::parse($request->deliver_time)->format('Y-m-d H:i:s') : null,
                    'arrive_time' => strlen($request->arrive_time) ? Carbon::parse($request->arrive_time)->format('Y-m-d H:i:s') : null,
                    'trip_rial_fare' => $request->trip_rial_fare,
                    'status' => $request->status,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'description' => $request->description,
                    'manager_description' => $request->manager_description,

                ]
            );


        return $this->sendResponse($trip, Lang::get('http-statuses.200'));
    }


    /**
     *
     * @OA\Get(
     *     path="/api/v1/admin/trip",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     summary="Get a list of trips",
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
    public function getAll(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $res = Trip::with(['store', 'vehicle', 'origin', 'destination'])->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     * * @OA\Get(
     *     path="/api/v1/admin/trip/{code}",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     summary="Get a trip by its code",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Trip not found",
     *     )
     * )
     */
    public function get(Request $request, $code)
    {
        $res = Trip::with(['store', 'vehicle', 'origin', 'destination'])->where('trip_code', '=', $code)->firstOrFail();
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     ** @OA\Get(
     *     path="/api/v1/vehicle/trip/{code}",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     summary="Get trip details by trip code",
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Trip not found",
     *     )
     * )
     */
    public function details(Request $request, $code)
    {

        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }
        $res = Trip::with(['store', 'vehicle', 'origin', 'destination'])
            ->where('trip_code', '=', $code)
            ->where(function ($query) use ($vehicle) {
                $query->whereNull('vehicle_id')
                    ->orWhere('vehicle_id', '=', $vehicle->id);
            })
            ->get();

        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }


    /**
     * @OA\Post(
     *     path="/api/v1/vehicle/trip/{code}/accept",
     *     summary="Accept a trip by its code",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Trip not found",
     *     )
     * )
     */
    public function acceptTripByVehicle(Request $request, $code)
    {

        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $flag = false;
        DB::beginTransaction();
        $trip = Trip::lockForUpdate()->where('trip_code', '=', $code)
            ->where(function ($query) use ($vehicle) {
                $query->whereNull('vehicle_id')
                    ->orWhere('vehicle_id', '=', $vehicle->id);
            })
            ->firstOrFail();

        if ($trip->status == 1) {
            $trip->vehicle_id = $vehicle->id;
            $trip->status = 2;
            $trip->save();
            $flag = true;
        }
        DB::commit();

        if (!$flag) {
            return $this->sendError('این سفر قبلا تعیین وضعیت شده است.', ['errors' => ['status' => Lang::get('http-statuses.400')]], 400);
        }

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vehicle/trip/{code}/waiting",
     *     summary="Waiting a trip by its code",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Trip not found",
     *     )
     * )
     */
    public function waitingToReceiveThePackageByVehicle(Request $request, $code)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $flag = false;
        DB::beginTransaction();
        $trip = Trip::lockForUpdate()->where('trip_code', '=', $code)
            ->where(function ($query) use ($vehicle) {
                $query->where('vehicle_id', '=', $vehicle->id);
            })
            ->firstOrFail();

        if ($trip->status == 2) {
            $trip->vehicle_id = $vehicle->id;
            $trip->status = 3;
            $trip->save();
            $flag = true;
        }
        DB::commit();

        if (!$flag) {
            return $this->sendError('این سفر قبلا تعیین وضعیت شده است.', ['errors' => ['status' => Lang::get('http-statuses.400')]], 400);
        }

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vehicle/trip/{code}/on-the-way",
     *     summary="on the way a trip by its code",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Trip not found",
     *     )
     * )
     */
    public function onTheWayTripByVehicle(Request $request, $code)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $flag = false;
        DB::beginTransaction();
        $trip = Trip::lockForUpdate()->where('trip_code', '=', $code)
            ->where(function ($query) use ($vehicle) {
                $query->where('vehicle_id', '=', $vehicle->id);
            })
            ->firstOrFail();

        if ($trip->status == 3) {
            $trip->vehicle_id = $vehicle->id;
            $trip->status = 4;
            $trip->save();
            $flag = true;
        }
        DB::commit();

        if (!$flag) {
            return $this->sendError('این سفر قبلا تعیین وضعیت شده است.', ['errors' => ['status' => Lang::get('http-statuses.400')]], 400);
        }

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vehicle/trip/{code}/deliver",
     *     summary="Deliver a trip by its code",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Trip not found",
     *     )
     * )
     */
    public function deliverTripByVehicle(Request $request, $code)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $flag = false;
        DB::beginTransaction();
        $trip = Trip::lockForUpdate()->where('trip_code', '=', $code)
            ->where(function ($query) use ($vehicle) {
                $query->where('vehicle_id', '=', $vehicle->id);
            })
            ->firstOrFail();

        if ($trip->status == 4) {
            $trip->vehicle_id = $vehicle->id;
            $trip->status = 5;
            $trip->save();
            $flag = true;
        }
        DB::commit();

        if (!$flag) {
            return $this->sendError('این سفر قبلا تعیین وضعیت شده است.', ['errors' => ['status' => Lang::get('http-statuses.400')]], 400);
        }

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }


    /**
     * @OA\Post(
     *     path="/api/v1/vehicle/trip/{code}/cancel",
     *     summary="Cancel a trip by its code",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="The code of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Trip not found",
     *     )
     * )
     */
    public function cancelTripByVehicle(Request $request, $code)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $flag = false;
        DB::beginTransaction();
        $trip = Trip::lockForUpdate()->where('trip_code', '=', $code)
            ->where(function ($query) use ($vehicle) {
                $query->where('vehicle_id', '=', $vehicle->id);
            })
            ->firstOrFail();

        if ($trip->status != 5) {
            $trip->vehicle_id = $vehicle->id;
            $trip->status = 6;
            $trip->save();
            $flag = true;
        }
        DB::commit();

        if (!$flag) {
            return $this->sendError('این سفر قبلا تعیین وضعیت شده است.', ['errors' => ['status' => Lang::get('http-statuses.400')]], 400);
        }

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicle/trip",
     *     summary="Get a list of trips without a vehicle assigned",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             default=10,
     *             minimum=1,
     *             maximum=100
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             default=1,
     *             minimum=1
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
        $res = Trip::with(['store', 'origin', 'destination'])
            ->whereNull('vehicle_id')
            ->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicle/trip/my",
     *     summary="Get a list of trips with a vehicle assigned",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             default=10,
     *             minimum=1,
     *             maximum=100
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int32",
     *             default=1,
     *             minimum=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Filter with type : all or delivered or notDelivered or current",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             default="all"
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
    public function vehicleTrips(Request $request)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $filter = $request->input('filter', 'all');
        $res = Trip::with(['store', 'origin', 'destination'])->where('vehicle_id', '=', $vehicle->id);

        switch ($filter) {
            case 'delivered':
                $res->whereIn('status', [5]);
                break;
            case 'notDelivered':
                $res->whereNotIn('status', [5]);
                break;
            case 'current':
                $res->whereIn('status', [2, 3, 4]);
                break;
            default:
                break;
        }
        $res = $res->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /*
* Generate a random and unique code.
*
* @param int $length The length of the code to generate.
* @param string $prefix A prefix to add to the code (optional).
* @param string $suffix A suffix to add to the code (optional).
* @param string $chars A string of characters to use for generating the code (optional).
*
* @return string The generated code.
*/
    function generateUniqueCode($length = 8, $prefix = '', $suffix = '', $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $code = '';

        while (true) {
            // Generate a random code.
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }

            // Add the prefix and suffix.
            $code = $prefix . $code . $suffix;

            // Check if the code is unique.
            if (!Trip::where('trip_code', $code)->exists()) {
                break;
            }

            // If the code is not unique, reset it and try again.
            $code = '';
        }

        return $code;
    }
}
