<?php

namespace App\Http\Controllers\API;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use App\Models\Log;
use App\Models\Vehicle;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class VehicleController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/v1/vehicle",
     *     summary="Store a new vehicle",
     *     description="Create a new vehicle record",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreVehicleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Vehicle")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Vehicle already exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Vehicle already exists."
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreVehicleRequest $request)
    {
        $user = User::find(Auth::id());
        $user->load('vehicle');
        if ($user->vehicle) {
            return $this->sendError('وسیله نقلیه قبلا ایجاد شده است.', ['error' => ['vehicle' => 'وسیله نقلیه قبلا ایجاد شده است.']], 409);
        }
        $input = $request->all();
        $input['user_id'] = $user->id;
        $vehicle = Vehicle::create($input);

        Log::store(LogUserTypesEnum::USER, Auth::id(), LogModelsEnum::VEHICLE, LogActionsEnum::ADD, json_encode($vehicle));

        $user->status = 2;
        $user->save();

        return $this->sendResponse($vehicle, ".وسیله نقلیه با موفقیت انجام شد\\nمنتظر تایید ادمین باشید");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/vehicle",
     *     summary="update a new vehicle",
     *     description="update vehicle record",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateVehicleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Vehicle")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable entity",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function update(UpdateVehicleRequest $request)
    {
        $user = User::find(Auth::id());
        $input = $request->all();
        $vehicle = Vehicle::where('user_id', Auth::id())->first();
        if ($vehicle) {
            $oldData = $vehicle->toArray();
            $vehicle->update($input);
            $newData = $vehicle->toArray();

            (new Vehicle())->logVehicleModelChanges($user, $oldData, $newData);

            $user->status = 2;
            $user->save();
        } else
            return $this->sendError('', 'وسیله نقلیه یافت نشد', 404);

        return $this->sendResponse($vehicle, ".وسیله نقلیه با موفقیت انجام شد\\nمنتظر تایید ادمین باشید");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/vehicle",
     *     summary="Delete the user's vehicle",
     *     description="Deletes the vehicle associated with the authenticated user.",
     *     operationId="deleteVehicle",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Response(
     *         response=200,
     *         description="Vehicle deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Vehicle deleted successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vehicle not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Vehicle not found."
     *             )
     *         )
     *     )
     * )
     */
    public function delete(Request $request)
    {
        $user = User::find(Auth::id());
        $user->load('vehicle');
        $vehicle = $user->vehicle;
        if ($vehicle) {
            $vehicle->delete();
            Log::store(LogUserTypesEnum::USER, Auth::id(), LogModelsEnum::VEHICLE, LogActionsEnum::DELETE, json_encode($vehicle));
            return $this->sendResponse('', 'وسیله نقلیه با موفقیت حذف شد');
        } else
            return $this->sendError('وسیله نقلیه پیدا نشده.', '', 404);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicle/my",
     *     summary="Get user's vehicle details",
     *     description="Returns the details of the vehicle associated with the authenticated user",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Vehicle")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function my(Request $request)
    {
        $user = User::find(Auth::id());
        $user->load('vehicle');
        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::VEHICLE, LogActionsEnum::VIEW_DETAILS);
        return $this->sendResponse($user->vehicle, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/vehicle/types",
     *     summary="Get vehicle types",
     *     description="Returns a list of vehicle types",
     *     security={ {"sanctum": {} }},
     *     tags={"Vehicle"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 example="motor"
     *             ),
     *             @OA\Items(
     *                 type="string",
     *                 example="car"
     *             )
     *         )
     *     ),
     * )
     */
    public function types()
    {
        $v = new Vehicle();
        return $this->sendResponse($v->types(), Lang::get('http-statuses.200'));
    }
}
