<?php

namespace App\Http\Controllers\API;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use App\Models\Log;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class StoreController extends BaseController
{

    /**
     * @OA\Post(
     *     path="/api/v1/store",
     *     summary="Create a new store",
     *     description="Creates a new store for the authenticated user.",
     *     operationId="createStore",
     *     tags={"Store"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Store data",
     *         @OA\JsonContent(ref="#/components/schemas/StoreStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Store")
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
     *         description="Store already exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Store already exists."
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreStoreRequest $request)
    {
        $user = User::find(Auth::id());
        $user->load('store');
        if ($user->store) {
            return $this->sendError('مغازه قبلا ایجاد شده است.', '', 409);
        }
        $input = $request->all();
        $input['user_id'] = $user->id;
        $store = Store::create($input);

        Log::store(LogUserTypesEnum::USER, Auth::id(), LogModelsEnum::STORE, LogActionsEnum::ADD, json_encode($store));

        $user->status = 0;
        $user->save();

        return $this->sendResponse($store, ".مغازه با موفقیت انجام شد\\nمنتظر تایید ادمین باشید");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/store",
     *     summary="Update a store",
     *     tags={"Store"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Store data",
     *         @OA\JsonContent(ref="#/components/schemas/StoreStoreRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Store")
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
    public function update(UpdateStoreRequest $request)
    {
        $user = User::find(Auth::id());
        $input = $request->all();
        $store = Store::where('user_id', Auth::id())->first();
        if ($store) {
            $oldData = $store->toArray();
            $store->update($input);
            $newData = $store->toArray();

            (new Store())->logStoreModelChanges($user, $oldData, $newData);

            $user->status = 0;
            $user->save();
        } else
            return $this->sendError('', 'مغازه یافت نشد', 404);

        return $this->sendResponse($store, ".مغازه با موفقیت انجام شد\\nمنتظر تایید ادمین باشید");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/store",
     *     summary="Delete the user's store",
     *     description="Deletes the store associated with the authenticated user.",
     *     operationId="deleteStore",
     *     security={ {"sanctum": {} }},
     *     tags={"Store"},
     *     @OA\Response(
     *         response=200,
     *         description="Store deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Store deleted successfully"
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
     *         description="Store not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Store not found."
     *             )
     *         )
     *     )
     * )
     */
    public function delete(Request $request)
    {
        $user = User::find(Auth::id());
        $user->load('store');
        $store = $user->store;
        if ($store) {
            $store->delete();
            Log::store(LogUserTypesEnum::USER, Auth::id(), LogModelsEnum::STORE, LogActionsEnum::DELETE, json_encode($store));
            return $this->sendResponse('', 'مغازه با موفقیت حذف شد');
        } else
            return $this->sendError('مغازه پیدا نشده.', '', 404);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/store/my",
     *     summary="Get user's store details",
     *     description="Returns the details of the store associated with the authenticated user",
     *     security={ {"sanctum": {} }},
     *     tags={"Store"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Store")
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
        $user->load('store');
        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::STORE, LogActionsEnum::VIEW_DETAILS);
        return $this->sendResponse($user->store, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/store/areaTypes",
     *     summary="Get area types",
     *     security={ {"sanctum": {} }},
     *     description="Returns a list of store area types",
     *     security={ {"sanctum": {} }},
     *     tags={"Store"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="string",
     *                 example="rent"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    public function areaTypes()
    {
        $v = new Store();
        return $this->sendResponse($v->areaTypes(), Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/store/categories",
     *     summary="Get store categories",
     *     description="Returns a list of store category",
     *     security={ {"sanctum": {} }},
     *     tags={"Store"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="title", type="string"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     * )
     */
    public function categories()
    {
        return $this->sendResponse(StoreCategory::select('id', 'title')->get(), Lang::get('http-statuses.200'));
    }
}
