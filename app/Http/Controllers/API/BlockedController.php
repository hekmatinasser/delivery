<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Models\Store;
use App\Models\StoresBlocked;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

class BlockedController  extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/store/block",
     *     summary="Get a list of vehicle blocked with user",
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
    public function getBlockedVehicleWithStore(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $filter = $request->input('filter', 'all');

        $user = User::find(Auth::id());
        $user->load('store');

        if (!$user->store) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }
        $userId = Auth::id();
        $res = StoresBlocked::with(['vehicle'])
            ->where('store_id', '=', $user->store->id)
            ->where('user_id', '=', $userId);

        $res = $res->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/store/block",
     *     summary="Blocked the vehicle",
     *     description="Add the vehicle to blocked list",
     *     tags={"Store"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="expire", type="date"),
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
    public function addBlockedVehicleWithStore(Request $request, $id)
    {
        $user = User::find(Auth::id());
        $user->load('store');

        if (!$user->store) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $selectedUser = User::where('id', '=', $request->get('user_id', 0))->with('vehicle')->whereHas('vehicle')->firstOrFail();

        StoresBlocked::create(
            [
                'vehicle_id' => $selectedUser->vehicle->id,
                'user_id' => Auth::id(),
                'store_id' => $user->store->id,
                'expire' => $request->get('expire', null)
            ]
        );

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/store/block/{id}",
     *     summary="Delete blocked vehicle by ID",
     *     description="Deletes a blocked vehicle by ID",
     *     operationId="blockedId",
     *     tags={"Store"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of blocked record",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     )
     * )
     */
    public function deleteBlockedVehicleWithStore(Request $request, $id)
    {
        $block = StoresBlocked::where('id', '=', $id)
            ->where('user_id', '=', Auth::id())
            ->firstOrFail();
        return $block->delete();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicle/block",
     *     summary="Get a list of store blocked with user",
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
    public function getBlockedStoreWithVehicle(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $filter = $request->input('filter', 'all');

        $user = User::find(Auth::id());
        $user->load('vehicle');

        $res = StoresBlocked::with(['vehicle'])
            ->where('vehicle_id', '=', $user->vehicle->id)
            ->where('user_id', '=', $$user->id);

        $res = $res->paginate($perPage, ['*'], 'page', $page);
        return $this->sendResponse($res, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/vehicle/block",
     *     summary="Blocked the store",
     *     description="Add the store to blocked list",
     *     tags={"Vehicle"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="expire", type="date"),
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
    public function addBlockedStoreWithVehicle(Request $request, $id)
    {
        $user = User::find(Auth::id());
        $user->load('vehicle');

        if (!$user->vehicle) {
            return $this->sendError(Lang::get('http-statuses.404'), '', 404);
        }

        $selectedUser = User::where('id', '=', $request->get('user_id', 0))->with('store')->whereHas('store')->firstOrFail();

        StoresBlocked::create(
            [
                'vehicle_id' => $user->vehicle->id,
                'user_id' => Auth::id(),
                'store_id' => $selectedUser->store->id,
                'expire' => $request->get('expire', null)
            ]
        );

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vehicle/block/{id}",
     *     summary="Delete blocked vehicle by ID",
     *     description="Deletes a blocked vehicle by ID",
     *     operationId="blockedStoreId",
     *     tags={"Vehicle"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of blocked record",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     )
     * )
     */
    public function deleteBlockedStoreWithVehicle(Request $request, $id)
    {
        $block = StoresBlocked::where('id', '=', $id)->where('user_id', '=', Auth::id())
            ->firstOrFail();
        return $block->delete();
    }
}
