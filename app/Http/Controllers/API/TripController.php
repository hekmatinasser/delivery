<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTripRequest;
use App\Http\Requests\Admin\UpdateTripRequest;
use App\Http\Requests\CoinWallet\StoreCoinWalletTransactionRequest;
use App\Http\Requests\Store\CreateTripRequest;
use App\Http\Resources\TripChangesResource;
use App\Models\NeighborhoodsAvailable;
use App\Models\Store;
use App\Models\Trip;
use App\Models\TripChange;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Setting;
use Carbon\Carbon;
use Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class TripController extends BaseController
{
    /**
     *
     * @OA\Get(
     *     path="/api/v1/admin/trip/{tripId}/changes",
     *     tags={"Trip"},
     *     security={ {"sanctum": {} }},
     *     summary="Get a list of trips",
     *     @OA\Parameter(
     *         name="tripId",
     *         in="path",
     *         description="The Id of the trip",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
    public function tripChanges(Request $request, $tripId)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $tripChanges = TripChange::where('trip_id', $tripId)->paginate($perPage, ['*'], 'page', $page);
        // return $this->sendResponse($res, Lang::get('http-statuses.200'));
        // $tripChanges = TripChangesResource::collection(TripChange::
        // ->latest()->get());
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
        $vehicle = Vehicle::find($request->vehicle_id);
        $trip =
            Trip::create(
                [
                    'trip_code' => $this->generateUniqueCode(),
                    'store_id' => $request->store_id,
                    'vehicle_type' => $vehicle->type == 'MOTOR' ? 0 : 1,
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
                    'expire' => Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s'),
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

        $vehicle = Vehicle::find($request->vehicle_id);

        $trip =
            Trip::updateOrCreate(
                ['id' => $trip->id],
                [
                    'store_id' => $request->store_id,
                    'vehicle_id' => $request->vehicle_id,
                    'vehicle_type' => $vehicle?->type == 'MOTOR' ? 0 : 1,
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
                    'expire' => Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s'),
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
     * 
     *     @OA\Parameter(
     *         name="filter",
     *         in="query",
     *         description="Filter with type : all or delivered or notDelivered or current or active or cancel",
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
    public function getAll(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $filter = $request->input('filter', 'all');
        $res = Trip::with(['store', 'vehicle', 'origin', 'destination']);
        switch ($filter) {
            case 'delivered':
                $res->whereIn('status', [5]);
                break;
            case 'notDelivered':
                $res->whereNotIn('status', [5]);
                break;
            case 'pending':
                $res->whereIn('status', [1]);
                break;
            case 'active':
                $res->whereIn('status', [1, 2, 3, 4]);
                break;
            case 'cancel':
                $res->whereIn('status', [6]);
                break;
            default:
                break;
        }
        $res = $res->paginate($perPage, ['*'], 'page', $page);
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
            ->first();

        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     ** @OA\Get(
     *     path="/api/v1/store/trip/{code}",
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
    public function detailsForStore(Request $request, $code)
    {
        $store = Store::where('user_id', Auth::id())->first();
        if (!$store) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }
        $res = Trip::with(['store', 'vehicle', 'origin', 'destination'])
            ->where('trip_code', '=', $code)
            ->where(function ($query) use ($store) {
                $query->where('store_id', '=', $store->id);
            })
            ->firstOrFail();

        if ($res) {
            $setting = Setting::first();
            if ($res->status == 1 && $res->expire < now()) {
                if ($res->expire < now()->subMinutes($setting->travel_expire_pending_time)) {
                    $res->delete();
                    $res = null;
                }
            }

            // if state is 1
            //      check expire
            //      if expired ? check max active time on setting
            // if not active remove it


        }

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
        $vehicle = Vehicle::with(['storesAvailable', 'storesBlocked'])->where('user_id', Auth::id())->firstOrFail();
        if (!$vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }
        $user = Auth::user();
        $coins = $user->load('coinWallet');
        $coin = $coins->coinWallet->coins;

        if ($user->status != 1) {
            $message = 'شما امکان تایید سفر را ندارید!';
            return $this->sendError($message, ['errors' => ['status' => $message]], 400);
        }

        if (!$coin) {
            $message = '(عدم موجودی سکه) شما امکان تایید سفر را ندارید!';
            return $this->sendError($message, ['errors' => ['status' => $message]], 400);
        }

        $storeAvailableIds = $vehicle->storesAvailable->pluck('store_id');
        $storeBlockedIds = $vehicle->storesBlocked->pluck('store_id');
        $neighborhoodIds = NeighborhoodsAvailable::where('vehicle_id', '=', $vehicle->id)->pluck('neighborhood_id');


        $flag = false;

        DB::beginTransaction();
        $trip = Trip::lockForUpdate()
            ->with('store')
            ->where('trip_code', '=', $code)
            ->where('vehicle_type', '=', $vehicle->type == 'CAR' ? 1 : 0)
            ->where(function ($query) use ($vehicle) {
                $query->whereNull('vehicle_id')
                    ->orWhere('vehicle_id', '=', $vehicle->id);
            })
            ->whereHas('store', function ($query) use ($neighborhoodIds, $storeAvailableIds, $storeBlockedIds) {
                $query->whereNotIn('id', $storeBlockedIds)
                    ->where(function ($q) use ($neighborhoodIds, $storeAvailableIds) {
                        $q->whereIn('neighborhood_id', $neighborhoodIds)
                            ->orWhereIn('id', $storeAvailableIds);
                    });
            })
            ->firstOrFail();
        $setting = Setting::first();
        $_expire = now()->subMinutes($setting->travel_expire_pending_time);

        if ($trip->status == 1 && $trip->expire >= $_expire) {
            $trip->vehicle_id = $vehicle->id;
            $trip->status = 2;
            $trip->save();
            $flag = true;
            $data = new StoreCoinWalletTransactionRequest();
            $setting = Setting::first();

            $data['user_id'] = Auth::id();
            $data['action'] = 'decrease';
            $data['coins'] = $setting->pay_coin_per_trip_with_vehicle;
            $data['reason_code'] = 21;

            (new CoinWalletController())->storeTransaction($data);

            $data['user_id'] = $trip->store->user_id;
            $data['coins'] = $setting->pay_coin_per_trip_with_store;

            (new CoinWalletController())->storeTransaction($data);
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

        $vehicle = Vehicle::with(['storesAvailable', 'storesBlocked'])->where('user_id', '=', Auth::id())->firstOrFail();

        $storeAvailableIds = $vehicle->storesAvailable->pluck('store_id');
        $storeBlockedIds = $vehicle->storesBlocked->pluck('store_id');
        $neighborhoodIds = NeighborhoodsAvailable::where('vehicle_id', '=', $vehicle->id)->pluck('neighborhood_id');
        $_date = Carbon::now()->toDateTimeString();
        $res = Trip::with(['store', 'origin', 'destination'])
            ->where('vehicle_type', '=', $vehicle->type == 'CAR' ? 1 : 0)
            ->where('expire', '>', $_date)
            ->whereNull('vehicle_id')
            ->where('status', 1)
            ->whereHas('store', function ($query) use ($neighborhoodIds, $storeAvailableIds, $storeBlockedIds) {
                $query->with('neighborhood')->whereNotIn('id', $storeBlockedIds)
                    ->where(function ($q) use ($neighborhoodIds, $storeAvailableIds) {
                        $q->whereIn('neighborhood_id', $neighborhoodIds)
                            ->orWhereIn('id', $storeAvailableIds);
                    });
            })
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
            case 'pending':
                $res->whereIn('status', [1]);
                break;
            case 'active':
                $res->whereIn('status', [1, 2, 3, 4]);
                break;
            case 'old':
                $res->whereIn('status', [6, 5]);
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

    /**
     * @OA\Post(
     *     path="/api/v1/store/trip",
     *     summary="Create a new trip with store",
     *     tags={"Store"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateTripRequest")
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
    public function createTripWithStore(CreateTripRequest $request)
    {
        $user = User::find(Auth::id());
        $user->load(['store', 'coinWallet']);
        if (!$user->store)
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);

        if ($user->status != 1) {
            $message = 'شما امکان ثبت سفر را ندارید! (وضعیت غیر فعال)';
            return $this->sendError($message, ['errors' => ['status' => $message]], 400);
        }

        $setting = Setting::first();
        if ($user->coinWallet->coins < $setting->pay_coin_per_trip_with_store) {
            $message = "شما امکان ثبت سفر را ندارید! (حداقل سکه برای درخواست $setting->pay_coin_per_trip_with_store  عدد است.)";
            return $this->sendError($message, ['errors' => ['status' => $message]], 400);
        }

        $setting = Setting::first();

        $trip =
            Trip::create(
                [
                    'trip_code' => $this->generateUniqueCode(),
                    'store_id' => $user->store->id,
                    'vehicle_type' => $request->vehicle_type,
                    'origin' => $user->store->neighborhood_id,
                    'destination' => $request->destination_id,
                    'request_registration_time' => Carbon::now()->format('Y-m-d H:i:s'),
                    'shipment_prepare_time' => Carbon::parse($request->shipment_prepare_time)->format('Y-m-d H:i:s'),
                    'trip_rial_fare' => $request->trip_rial_fare,
                    'status' => 1,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'description' => $request->description,
                    'expire' => Carbon::now()->addMinutes($setting->travel_expire_approve_time)->format('Y-m-d H:i:s'),
                ]
            );


        return $this->sendResponse($trip, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/store/trip/{code}",
     *     summary="Update a trip",
     *     tags={"Store"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateStoreTripRequest")
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
    public function updateTripWithStore(UpdateTripRequest $request, $code)
    {
        $user = User::find(Auth::id());
        $user->load('store');
        if (!$user->store)
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);

        if ($user->status != 1) {
            $message = 'شما امکان افزودن  سفر جدید را ندارید!';
            return $this->sendError($message, ['errors' => ['status' => $message]], 400);
        }

        $trip = Trip::where('trip_code', '=', $code)->where('store_id', '=', $user->store->id)->firstOrFail();

        $trip =
            Trip::updateOrCreate(
                ['id' => $trip->id],
                [
                    // 'vehicle_type' => $request->vehicle_type,
                    // 'destination' => $request->destination_id,
                    // 'shipment_prepare_time' => Carbon::parse($request->shipment_prepare_time)->format('Y-m-d H:i:s'),
                    // 'trip_rial_fare' => $request->trip_rial_fare,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'description' => $request->description,
                    // 'manager_description' => $request->manager_description,
                    // 'expire' => Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s'),

                ]
            );


        return $this->sendResponse($trip, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/store/trip/{code}/cancel",
     *     summary="Cancel a trip",
     *     tags={"Store"},
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
    public function cancelTripWithStore(Request $request, $code)
    {
        $user = User::find(Auth::id());
        $user->load('store');
        if (!$user->store)
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);

        if ($user->status != 1) {
            $message = 'شما امکان ویرایش سفر را ندارید!';
            return $this->sendError($message, ['errors' => ['status' => $message]], 400);
        }

        $trip = Trip::where('trip_code', '=', $code)->where('store_id', '=', $user->store->id)->firstOrFail();

        $trip->status = 6;
        $trip->save();


        return $this->sendResponse($trip, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/store/trip/my",
     *     summary="Get a list of trips with a store assigned",
     *     security={ {"sanctum": {} }},
     *     tags={"Store"},
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
     *         description="Filter with type : all or delivered or notDelivered or pending",
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
    public function storeTrips(Request $request)
    {
        $store = Store::where('user_id', Auth::id())->first();
        if (!$store) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $filter = $request->input('filter', 'all');

        $setting = Setting::first();
        $_expire = now()->subMinutes($setting->travel_expire_pending_time);
        $res = Trip::with(['store', 'origin', 'destination'])
            // ->whereDate('expire', '>=', $_expire)
            ->where('store_id', '=', $store->id);

        switch ($filter) {
            case 'delivered':
                $res->whereIn('status', [5]);
                break;
            case 'notDelivered':
                $res->whereNotIn('status', [5]);
                break;
            case 'pending':
                $res->whereIn('status', [1]);
                break;
            case 'active':
                $res->whereIn('status', [1, 2, 3, 4]);
                break;
            case 'old':
                $res->whereIn('status', [6, 5]);
                break;
            default:
                break;
        }
        $res = $res->orderBy('request_registration_time', 'DESC')->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }
}