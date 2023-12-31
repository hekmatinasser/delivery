<?php

namespace App\Http\Controllers\API;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateEmployeeRequest;
use App\Http\Requests\Admin\CreateStoreRequest;
use App\Http\Requests\Admin\CreateVehicleRequest;
use App\Http\Requests\Admin\GetEmployeesRequest;
use App\Http\Requests\Admin\GetStoresRequest;
use App\Http\Requests\Admin\GetVehicleRequest;
use App\Http\Requests\Admin\GetVehiclesRequest;
use App\Http\Requests\Admin\UpdateStoreRequest;
use App\Http\Requests\Admin\UpdateVehicleRequest;
use App\Http\Requests\Admin\UpdateVehicleAccessRequest;
use App\Models\Log;
use Illuminate\Support\Facades\Log as LogManager;
use App\Models\Neighborhood;
use App\Models\NeighborhoodsAvailable;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RoleUser;
use App\Models\Store;
use App\Models\StoreAvailable;
use App\Models\StoresBlocked;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VerifyCode;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Morilog\Jalali\Jalalian;
use Spatie\FlareClient\Http\Exceptions\InvalidData;
use Symfony\Component\HttpKernel\Log\Logger;

class AdminController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/admin/employee",
     *     summary="Create a new employee",
     *     description="Creates a new employee with the given information",
     *     operationId="createEmployee",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateEmployeeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function createEmployee(CreateEmployeeRequest $request)
    {
        $input = $request->all();
        $role = Role::where('name', $request->role)->first();
        if (!$role) {
            return $this->sendError('مقدار نقش کاربر اشتباه است', ['errors' => ['role' => 'مقدار نقش کاربر اشتباه است']], 422);
        }
        if ($request->nationalCode)
            if (!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['errors' => ['nationalCode' => 'کد ملی معتبر نمی باشد']], 422);

        $input['userType'] = '1';
        $input['password'] = bcrypt($request->password);

        if ($request->hasFile('nationalPhoto')) {
            // $path = $request->file('nationalPhoto')->store('national_photos');

            $path = uploadNationalImageToS3($request->file('nationalPhoto'));

            $input['nationalPhoto'] = $path;
        }
        $user = User::create($input);
        $user->wallet()->create();
        $user->coinWallet()->create();

        RoleUser::create(['user_id' => $user->id, 'role_id' => $role->id]);
        Log::store(LogUserTypesEnum::ADMIN, $user->id, LogModelsEnum::REGISTER, LogActionsEnum::REQUEST, json_encode($user));

        return $this->sendResponse($user, 'کاربر با موفقیت ایجاد شد.');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/employee",
     *     summary="Get a list of employees",
     *     description="Returns a paginated list of employees with their roles",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
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
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="current_page",
     *                 type="integer",
     *                 description="The current page number"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             ),
     *             @OA\Property(
     *                 property="first_page_url",
     *                 type="string",
     *                 description="The URL for the first page of results"
     *             ),
     *             @OA\Property(
     *                 property="from",
     *                 type="integer",
     *                 description="The index of the first item in the current page of results"
     *             ),
     *             @OA\Property(
     *                 property="last_page",
     *                 type="integer",
     *                 description="The total number of pages of results"
     *             ),
     *             @OA\Property(
     *                 property="last_page_url",
     *                 type="string",
     *                 description="The URL for the last page of results"
     *             ),
     *             @OA\Property(
     *                 property="next_page_url",
     *                 type="string",
     *                 description="The URL for the next page of results"
     *             ),
     *             @OA\Property(
     *                 property="path",
     *                 type="string",
     *                 description="The URL path for the current page of results"
     *             ),
     *             @OA\Property(
     *                 property="per_page",
     *                 type="integer",
     *                 description="The number of results per page"
     *             ),
     *             @OA\Property(
     *                 property="prev_page_url",
     *                 type="string",
     *                 description="The URL for the previous page of results"
     *             ),
     *             @OA\Property(
     *                 property="to",
     *                 type="integer",
     *                 description="The index of the last item in the current page of results"
     *             ),
     *             @OA\Property(
     *                 property="total",
     *                 type="integer",
     *                 description="The total number of items in the result set"
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     * )
     */
    public function getEmployees(GetEmployeesRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $q = $request->input('q', null);
        $startDate = $request->input('startDate', null);
        $endDate = $request->input('endDate', null);
        $type = $request->input('type', null);
        $status = $request->input('status', null);

        if ($startDate) {
            $jDate = Jalalian::fromFormat('Y/m/d', convertNumbers($startDate));
            $startDate = $jDate->toCarbon();
        }

        if ($endDate) {
            $jDate = Jalalian::fromFormat('Y/m/d', convertNumbers($endDate));
            $endDate = $jDate->toCarbon();
        }

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::USER, LogActionsEnum::SHOW_ALL);

        $employees = User::with(['roles',])->where('userType', '1')
            ->where(function ($query) use ($q) {
                $query->where('id', 'like', '%' . $q . '%')
                    ->orWhere('users.name', 'like', '%' . $q . '%')
                    ->orWhere('users.family', 'like', '%' . $q . '%')
                    ->orWhere('users.nationalCode', 'like', '%' . $q . '%')
                    ->orWhere('users.mobile', 'like', '%' . $q . '%')
                    ->orWhere('users.email', 'like', '%' . $q . '%')
                    ->orWhere('users.employee_code', 'like', '%' . $q . '%')
                    ->orWhere('name', 'like', '%' . $q . '%');
            });

        if ($status) {
            $employees->where('users.status', '=', $status);
        }


        if ($startDate) {
            $employees->whereDate('users.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $employees->whereDate('users.created_at', '<=', $endDate);
        }


        return $employees->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/employee/{employeeId}",
     *     summary="Get employee by ID",
     *     description="Returns a single employee by ID",
     *     operationId="getEmployeeById",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="employeeId",
     *         in="path",
     *         description="ID of employee to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     )
     * )
     */
    public function getEmployee(Request $request, $employeeId)
    {
        $user = User::with(['roles', 'createdBy'])->where('userType', '1')->where('id', $employeeId)->firstOrFail();
        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::USER, LogActionsEnum::SHOW_PROFILE, $user);
        return $user;
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/employee/{employeeId}",
     *     summary="Update employee by ID",
     *     description="Updates a single employee by ID",
     *     operationId="updateEmployeeById",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="employeeId",
     *         in="path",
     *         description="ID of employee to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Employee object that needs to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEmployeesRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity",
     *     ),
     * )
     */
    public function updateEmployee(Request $request, $employeeId)
    {
        $user = $this->getEmployee($request, $employeeId);

        if ($request->nationalCode)
            if (!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['errors' => ['nationalCode' => 'کد ملی معتبر نمی باشد']], 422);

        $input = $request->all();

        $oldData = $user->toArray();
        $user->update($input);
        $newData = $user->toArray();
        if ($oldData['status'] != $newData['status']) {
            updateUserStatusNotice($user->mobile, $user->status);
        }

        (new User())->logUserModelChanges(Auth::user(), $oldData, $newData);

        return $this->sendResponse($newData, ".بروزرسانی با موفقیت انجام شد");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/employee/{employeeId}",
     *     summary="Delete employee by ID",
     *     description="Deletes a single employee by ID",
     *     operationId="deleteEmployeeById",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="employeeId",
     *         in="path",
     *         description="ID of employee to delete",
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
    public function deleteEmployee(Request $request, $employeeId)
    {
        $user = $this->getEmployee($request, $employeeId);

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::USER, LogActionsEnum::DELETE, $user);

        return $user->delete();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/store",
     *     summary="Create a new store",
     *     description="Creates a new store with the given information",
     *     operationId="createUserStore",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *             @OA\MediaType(
     *                      mediaType="multipart/form-data",
     *                      @OA\Schema(
     *                      @OA\Property(property="name", type="string", maxLength=70),
     *                      @OA\Property(property="family", type="string", maxLength=70),
     *                      @OA\Property(property="mobile", type="string", format="mobile", example="09123456789"),
     *                      @OA\Property(property="password", type="string", example="newPassword"),
     *                      @OA\Property(property="status", type="integer", example="0",description="User's status :: 1 => active, 0 => inactive, -1 => suspended, -2 => blocked"),
     *                      @OA\Property(property="nationalCode", type="string", format="nationalCode", example="0123456789"),
     *                      @OA\Property(property="nationalPhoto", type="string", format="binary", description="The user's national photo image file (JPEG or PNG format, max size 15MB, min dimensions 100x100, max dimensions 1000x1000)."),
     *                      @OA\Property(property="address", type="string", maxLength=255),
     *                      @OA\Property(property="postCode", type="string", format="postCode", example="1234567890"),
     *                      @OA\Property(property="phone", type="string", format="phone", example="1234567890"),
     *     @OA\Property(
     *         property="storeCategory_id",
     *         description="Category ID",
     *         type="integer",
     *         example="1"
     *     ),
     *     @OA\Property(
     *         property="neighborhood_id",
     *         description="Neighborhood ID",
     *         type="integer",
     *         example="1"
     *     ),
     *     @OA\Property(
     *         property="storeAreaType",
     *         description="Area Type 0 = RENT, 1 OWNERSHIP",
     *         type="string",
     *         enum={"0", "1"},
     *         example="0"
     *     ),
     *     @OA\Property(
     *         property="storeName",
     *         description="Name",
     *         type="string",
     *         example="John Doe"
     *     ),
     *     @OA\Property(
     *         property="storeAddress",
     *         description="Address",
     *         type="string",
     *         example="123 Main St"
     *     ),
     *     @OA\Property(
     *         property="storePostCode",
     *         description="Post Code",
     *         type="integer",
     *         example="1234567890"
     *     ),
     *     @OA\Property(
     *         property="storePhone",
     *         description="Phone",
     *         type="integer",
     *         example="1234567890"
     *     ),
     *     @OA\Property(
     *         property="storeLat",
     *         description="Latitude",
     *         type="number",
     *         format="desimal",
     *         example="40.7128"
     *     ),
     *     @OA\Property(
     *         property="storeLang",
     *         description="Longitude",
     *         type="number",
     *         format="desimal",
     *         example="-74.0060"
     *     ),
     *                 )
     *             )
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function createStore(CreateStoreRequest $request)
    {
        if ($request->nationalCode)
            if (!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['errors' => ['nationalCode' => 'کد ملی معتبر نمی باشد']], 422);

        $inputUser = $request->only(['name', 'family', 'mobile', 'nationalCode', 'address', 'postCode', 'phone', 'status']);


        $inputUser['userType'] = '0';
        $inputUser['password'] = bcrypt($request->password);

        if ($request->hasFile('nationalPhoto')) {
            // $path = $request->file('nationalPhoto')->store('national_photos');

            $path = uploadNationalImageToS3($request->file('nationalPhoto'));

            $inputUser['nationalPhoto'] = $path;
        }

        DB::beginTransaction();
        $user = User::create($inputUser);
        $user->wallet()->create();
        $user->coinWallet()->create();

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::USER, LogActionsEnum::ADD, json_encode($user));

        $store = Store::create([
            'user_id' => $user->id,
            'category_id' => $request->storeCategory_id,
            'areaType' => $request->storeAreaType,
            'name' => $request->storeName,
            'address' => $request->storeAddress,
            'postCode' => $request->storePostCode,
            'phone' => $request->storePhone,
            'lat' => $request->storeLat,
            'lang' => $request->storeLang,
            'neighborhood_id' => $request->neighborhood_id,
        ]);

        if ($request->get('sendNotice', false)) {
            registerNotice($request->get('mobile'), $request->get('password'));
        }

        Log::store(LogUserTypesEnum::USER, Auth::id(), LogModelsEnum::STORE, LogActionsEnum::ADD, json_encode($store));
        $user->save();
        $user->load('store', 'wallet', 'coinWallet');
        DB::commit();

        return $this->sendResponse($user, ".مغازه با موفقیت انجام شد");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/store",
     *     summary="Get all stores",
     *     description="Retrieve a paginated list of all stores.",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
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
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function getStores(GetStoresRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $q = $request->input('q', null);
        $startDate = $request->input('startDate', null);
        $endDate = $request->input('endDate', null);
        $type = $request->input('type', null);
        $status = $request->input('status', null);

        if ($startDate) {
            $jDate = Jalalian::fromFormat('Y/m/d', convertNumbers($startDate));
            $startDate = $jDate->toCarbon();
        }

        if ($endDate) {
            $jDate = Jalalian::fromFormat('Y/m/d', convertNumbers($endDate));
            $endDate = $jDate->toCarbon();
        }

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::STORE, LogActionsEnum::SHOW_ALL);
        $stores = User::with(['store', 'wallet', 'coinWallet', 'createdBy'])
            ->whereHas('store', function ($query) use ($startDate) {
                return $startDate ? $query->whereDate('users.created_at', '>=', $startDate)
                    : $query;
            })->whereHas('store', function ($query) use ($endDate) {
            return $endDate ? $query->whereDate('users.created_at', '<=', $endDate)
                : $query;
        })->whereHas('store', function ($query) use ($status) {
            return $status != null ? $query->where('users.status', '=', $status)
                : $query;
        })->whereHas('store', function ($query) use ($type) {
            return $type != null ? $query->where('category_id', '=', $type)
                : $query;
        })->whereHas('store', function ($query) use ($q) {
            return $q ? $query
                ->where('id', 'like', '%' . $q . '%')
                ->orWhere('users.name', 'like', '%' . $q . '%')
                ->orWhere('users.family', 'like', '%' . $q . '%')
                ->orWhere('users.nationalCode', 'like', '%' . $q . '%')
                ->orWhere('users.mobile', 'like', '%' . $q . '%')
                ->orWhere('name', 'like', '%' . $q . '%')
                : $query;
        });
        return $stores->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/store/{storeId}",
     *     summary="Get store",
     *     description="Get the profile information of a specific store.",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="storeId",
     *         in="path",
     *         description="The ID of the store to get.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function getStore(Request $request, $storeId)
    {
        $user = User::with('store')->whereHas('store', function ($query) use ($storeId) {
            $query->where('id', $storeId);
        })->firstOrFail();

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::STORE, LogActionsEnum::SHOW_PROFILE, $user);
        return $user;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/store/{storeId}/update",
     *     summary="Update store",
     *     description="Update the profile and store information of a specific store.",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="storeId",
     *         in="path",
     *         description="The ID of the store to update.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *             @OA\MediaType(
     *                      mediaType="multipart/form-data",
     *                      @OA\Schema(
     *                      @OA\Property(property="name", type="string", maxLength=70),
     *                      @OA\Property(property="family", type="string", maxLength=70),
     *                      @OA\Property(property="mobile", type="string", format="mobile", example="09123456789"),
     *                      @OA\Property(property="password", type="string", example="newPassword"),
     *                      @OA\Property(property="status", type="integer", example="0",description="User's status :: 1 => active, 0 => inactive, -1 => suspended, -2 => blocked"),
     *                      @OA\Property(property="nationalCode", type="string", format="nationalCode", example="0123456789"),
     *                      @OA\Property(property="nationalPhoto", type="string", format="binary", description="The user's national photo image file (JPEG or PNG format, max size 15MB, min dimensions 100x100, max dimensions 1000x1000)."),
     *                      @OA\Property(property="address", type="string", maxLength=255),
     *                      @OA\Property(property="postCode", type="string", format="postCode", example="1234567890"),
     *                      @OA\Property(property="phone", type="string", format="phone", example="1234567890"),
     *     @OA\Property(
     *         property="storeCategory_id",
     *         description="Category ID",
     *         type="integer",
     *         example="1"
     *     ),
     *     @OA\Property(
     *         property="storeAreaType",
     *         description="Area Type",
     *         type="string",
     *         enum={"RENT", "OWNERSHIP"},
     *         example="RENT"
     *     ),
     *     @OA\Property(
     *         property="storeName",
     *         description="Name",
     *         type="string",
     *         example="John Doe"
     *     ),
     *     @OA\Property(
     *         property="storeAddress",
     *         description="Address",
     *         type="string",
     *         example="123 Main St"
     *     ),
     *     @OA\Property(
     *         property="storePostCode",
     *         description="Post Code",
     *         type="integer",
     *         example="1234567890"
     *     ),
     *     @OA\Property(
     *         property="storePhone",
     *         description="Phone",
     *         type="integer",
     *         example="1234567890"
     *     ),
     *     @OA\Property(
     *         property="storeLat",
     *         description="Latitude",
     *         type="number",
     *         format="desimal",
     *         example="40.7128"
     *     ),
     *     @OA\Property(
     *         property="storeLang",
     *         description="Longitude",
     *         type="number",
     *         format="desimal",
     *         example="-74.0060"
     *     ),
     *                 )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function updateStore(UpdateStoreRequest $request, $storeId)
    {
        if ($request->nationalCode)
            if (!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['errors' => ['nationalCode' => 'کد ملی معتبر نمی باشد']], 422);

        $inputUser = $request->only(['name', 'family', 'mobile', 'nationalCode', 'address', 'postCode', 'phone', 'status']);

        if ($request->password)
            $inputUser['password'] = bcrypt($request->password);

        $user = $this->getStore($request, $storeId);

        if ($request->hasFile('nationalPhoto')) {
            // $path = $request->file('nationalPhoto')->store('national_photos');

            if ($user->nationalPhoto) {
                Storage::disk('liara')->delete($user->nationalPhoto);
            }
            $path = uploadNationalImageToS3($request->file('nationalPhoto'));

            $inputUser['nationalPhoto'] = $path;
        }


        // if (Validator::make($inputUser, ['mobile' => ])->failed()) {
        //     return $this->sendError('شماره همراه قبلا انتخاب شده است.', ['errors' => ['mobile' => 'شماره همراه قبلا انتخاب شده است.']], 422);
        // }
        $request->validate([
            'mobile' => 'unique:users,mobile,' . $user->id
        ]);

        DB::beginTransaction();

        $oldData = $user->toArray();
        $user->update($inputUser);
        $newData = $user->toArray();
        if ($oldData['status'] != $newData['status']) {
            updateUserStatusNotice($user->mobile, $user->status);
        }

        (new User())->logUserModelChanges(Auth::user(), $oldData, $newData);


        $store = $user->store;

        if ($store) {
            $oldData = $store->toArray();
            $data = $request->only(['storeCategory_id', 'storeAreaType', 'storeName', 'storePostCode', 'storePhone', 'storeLat', 'storeLang', 'neighborhood_id']);

            $store->update([
                'category_id' => $data['storeCategory_id'],
                'areaType' => $data['storeAreaType'],
                'name' => $data['storeName'],
                'postCode' => $data['storePostCode'],
                'storePhone' => $data['storePhone'],
                'lat' => $data['storeLat'] || 0,
                'lang' => $data['storeLang'] || 0,
                'neighborhood_id' => $data['neighborhood_id'],
            ]);
            $newData = $store->toArray();

            (new Store())->logStoreModelChanges($user, $oldData, $newData);
        }


        $user->load('store', 'wallet', 'coinWallet');
        DB::commit();

        return $this->sendResponse($user, ".مغازه با موفقیت انجام شد");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/store/{storeId}",
     *     summary="Delete store by ID",
     *     description="Deletes a single store by ID",
     *     operationId="deleteStoreById",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="storeId",
     *         in="path",
     *         description="ID of store to delete",
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
     *         description="Store not found"
     *     )
     * )
     */
    public function deleteStore(Request $request, $storeId)
    {
        $user = $this->getStore($request, $storeId);

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::STORE, LogActionsEnum::DELETE, $user);

        return $user->delete();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/vehicle",
     *     summary="Create a new vehicle",
     *     description="Creates a new vehicle with the given information",
     *     operationId="createUserVehicle",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *             @OA\MediaType(
     *                      mediaType="multipart/form-data",
     *                      @OA\Schema(
     *                      @OA\Property(property="name", type="string", maxLength=70),
     *                      @OA\Property(property="family", type="string", maxLength=70),
     *                      @OA\Property(property="mobile", type="string", format="mobile", example="09123456789"),
     *                      @OA\Property(property="password", type="string", example="newPassword"),
     *                      @OA\Property(property="sendNotice", type="boolean", example="true"),
     *                      @OA\Property(property="status", type="integer", example="0",description="User's status :: 1 => active, 0 => inactive, -1 => suspended, -2 => blocked"),
     *                      @OA\Property(property="nationalCode", type="string", format="nationalCode", example="0123456789"),
     *                      @OA\Property(property="nationalPhoto", type="string", format="binary", description="The user's national photo image file (JPEG or PNG format, max size 15MB, min dimensions 100x100, max dimensions 1000x1000)."),
     *                      @OA\Property(property="address", type="string", maxLength=255, example="address"),
     *                      @OA\Property(property="postCode", type="string", format="postCode", example="1234567890"),
     *                      @OA\Property(property="phone", type="string", format="phone", example="1234567890"),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="The type of the vehicle (MOTOR or CAR)",
     *         enum={"MOTOR", "CAR"}, example="MOTOR"
     *     ),
     *     @OA\Property(
     *         property="brand",
     *         type="string",
     *         description="The brand of the vehicle",
     *         maxLength=150, example="brand"
     *     ),
     *     @OA\Property(
     *         property="pelak",
     *         type="string",
     *         description="The pelak of the vehicle",
     *         maxLength=50, example="pelak"
     *     ),
     *     @OA\Property(
     *         property="color",
     *         type="string",
     *         description="The color of the vehicle",
     *         maxLength=50, example="color"
     *     ),
     *     @OA\Property(
     *         property="model",
     *         type="string",
     *         description="The model of the vehicle",
     *         maxLength=150, example="model"
     *     )
     *                 )
     *             )
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function createVehicle(CreateVehicleRequest $request)
    {
        if ($request->nationalCode)
            if (!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['errors' => ['nationalCode' => 'کد ملی معتبر نمی باشد']], 422);

        $inputUser = $request->only(['name', 'family', 'mobile', 'nationalCode', 'address', 'postCode', 'phone', 'status']);


        $inputUser['userType'] = '0';
        $inputUser['password'] = bcrypt($request->password);

        if ($request->hasFile('nationalPhoto')) {
            // $path = $request->file('nationalPhoto')->store('national_photos');

            try {
                $path = uploadNationalImageToS3($request->file('nationalPhoto'));

                $inputUser['nationalPhoto'] = $path;
            } catch (Exception $th) {
                return $this->sendError('خطایی در آپلود فایل اتفاق افتاد', ['errors' => ['nationalPhoto' => $th->getMessage()]], 422);
            }
        }

        DB::beginTransaction();
        $user = User::create($inputUser);
        $user->wallet()->create();
        $user->coinWallet()->create();

        if ($request->get('sendNotice', false)) {
            registerNotice($request->get('mobile'), $request->get('password'));
        }

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::USER, LogActionsEnum::ADD, json_encode($user));

        $input = $request->only(['model', 'type', 'brand', 'pelak', 'color']);
        $input['user_id'] = $user->id;
        $vehicle = Vehicle::create($input);

        Log::store(LogUserTypesEnum::USER, Auth::id(), LogModelsEnum::VEHICLE, LogActionsEnum::ADD, json_encode($vehicle));

        $user->load('vehicle', 'wallet', 'coinWallet');
        DB::commit();

        return $this->sendResponse($user, ".مغازه با موفقیت انجام شد");
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/vehicle",
     *     summary="Get all Vehicles",
     *     description="Retrieve a paginated list of all Vehicles.",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="The number of Vehicles to return per page (default 10).",
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
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function getVehicles(GetVehiclesRequest $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $q = $request->input('q', null);
        $startDate = $request->input('startDate', null);
        $endDate = $request->input('endDate', null);
        $vehicleType = $request->input('type', null);
        $status = $request->input('status', null);

        if ($startDate) {
            $jDate = Jalalian::fromFormat('Y/m/d', convertNumbers($startDate));
            $startDate = $jDate->toCarbon();
        }

        if ($endDate) {
            $jDate = Jalalian::fromFormat('Y/m/d', convertNumbers($endDate));
            $endDate = $jDate->toCarbon();
        }

        if ($vehicleType == 'CAR') {
            $vehicleType = 1;
        } else {
            $vehicleType = 0;
        }

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::VEHICLE, LogActionsEnum::SHOW_ALL);
        $vehicles = User::with(['vehicle', 'wallet', 'coinWallet', 'createdBy'])
            ->whereHas('vehicle', function ($query) use ($startDate) {
                return $startDate ? $query->whereDate('users.created_at', '>=', $startDate)
                    : $query;
            })->whereHas('vehicle', function ($query) use ($endDate) {
            return $endDate ? $query->whereDate('users.created_at', '<=', $endDate)
                : $query;
        })->whereHas('vehicle', function ($query) use ($vehicleType) {
            return $vehicleType != null ? $query->where('vehicle.type', '=', $vehicleType)
                : $query;
        })->whereHas('vehicle', function ($query) use ($status) {
            return $status != null ? $query->where('users.status', '=', $status)
                : $query;
        })->whereHas('vehicle', function ($query) use ($q) {
            return $q ? $query->where('id', 'like', '%' . $q . '%')
                ->orWhere('users.name', 'like', '%' . $q . '%')
                ->orWhere('users.family', 'like', '%' . $q . '%')
                ->orWhere('users.nationalCode', 'like', '%' . $q . '%')
                ->orWhere('users.mobile', 'like', '%' . $q . '%')
                ->orWhere('vehicle.brand', 'like', '%' . $q . '%')
                ->orWhere('vehicle.color', 'like', '%' . $q . '%')
                ->orWhere('vehicle.model', 'like', '%' . $q . '%')
                ->orWhere('vehicle.pelak', 'like', '%' . $q . '%')
                : $query;
        });
        return $vehicles->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/vehicle/{vehicleId}",
     *     summary="Get vehicle",
     *     description="Get the profile information of a specific vehicle.",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="vehicleId",
     *         in="path",
     *         description="The ID of the vehicle to get.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function getVehicle(Request $request, $vehicleId)
    {
        $userId = Vehicle::where('id', $vehicleId)->first()->user_id;
        $user = User::with([
            'vehicle' => function ($q) use ($userId) {
                return $q->with([
                    'storesAdminAccess',
                    'storesAdminBlock',
                    'storesBlockedWithUser' => function ($q) use ($userId) {
                        $q->where('user_id', '=', $userId);
                    },
                    'storesBlockedWithStore' => function ($q) use ($userId) {
                        $q->where('user_id', '<>', $userId);
                    },
                ]);
            }
        ])->whereHas('vehicle', function ($query) use ($vehicleId) {
            $query->where('id', $vehicleId);
        })->firstOrFail();

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::VEHICLE, LogActionsEnum::SHOW_PROFILE, $user);
        return $user;
    }

    public function getUserVehicle($vehicleId)
    {
        $userId = Vehicle::where('id', $vehicleId)->first()->user_id;
        $user = User::whereHas('vehicle', function ($query) use ($vehicleId) {
            $query->where('id', $vehicleId);
        })->firstOrFail();
        return $user;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/vehicle/{vehicleId}/update",
     *     summary="Update vehicle",
     *     description="Update the profile and vehicle information of a specific vehicle.",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="vehicleId",
     *         in="path",
     *         description="The ID of the vehicle to update.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *             @OA\MediaType(
     *                      mediaType="multipart/form-data",
     *                      @OA\Schema(
     *                      @OA\Property(property="name", type="string", maxLength=70),
     *                      @OA\Property(property="family", type="string", maxLength=70),
     *                      @OA\Property(property="mobile", type="string", format="mobile", example="09123456789"),
     *                      @OA\Property(property="password", type="string", example="newPassword"),
     *                      @OA\Property(property="status", type="integer", example="0",description="User's status :: 1 => active, 0 => inactive, -1 => suspended, -2 => blocked"),
     *                      @OA\Property(property="nationalCode", type="string", format="nationalCode", example="0123456789"),
     *                      @OA\Property(property="nationalPhoto", type="string", format="binary", description="The user's national photo image file (JPEG or PNG format, max size 15MB, min dimensions 100x100, max dimensions 1000x1000)."),
     *                      @OA\Property(property="address", type="string", maxLength=255,example="1234567890"),
     *                      @OA\Property(property="postCode", type="string", format="postCode", example="1234567890"),
     *                      @OA\Property(property="phone", type="string", format="phone", example="1234567890"),
     *     @OA\Property(
     *         property="storesBlockedWithStore",
     *         type="array",
     *         description="The blocked stores for the vehicle.",
     *         @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="expire", type="string", format="date")
     *         )
     *     ),
     *     @OA\Property(
     *         property="type",
     *         type="string",
     *         description="The type of the vehicle (MOTOR or CAR)",
     *         enum={"MOTOR", "CAR"},example="CAR"
     *     ),
     *     @OA\Property(
     *         property="brand",
     *         type="string",
     *         description="The brand of the vehicle",
     *         maxLength=150,example="brand1"
     *     ),
     *     @OA\Property(
     *         property="pelak",
     *         type="string",
     *         description="The pelak of the vehicle",
     *         maxLength=50,example="pelak1"
     *     ),
     *     @OA\Property(
     *         property="color",
     *         type="string",
     *         description="The color of the vehicle",
     *         maxLength=50,example="color1"
     *     ),
     *     @OA\Property(
     *         property="model",
     *         type="string",
     *         description="The model of the vehicle",
     *         maxLength=150,example="model1"
     *     )
     *                 )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function updateVehicle(UpdateVehicleRequest $request, $vehicleId)
    {
        if ($request->nationalCode)
            if (!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['errors' => ['nationalCode' => 'کد ملی معتبر نمی باشد']], 422);

        $inputUser = $request->only(['name', 'family', 'mobile', 'nationalCode', 'address', 'postCode', 'phone', 'status']);

        if ($request->password)
            $inputUser['password'] = bcrypt($request->password);

        $user = $this->getUserVehicle($vehicleId);

        if ($user->nationalPhoto || $user->nationalPhotoStatus == 'remove') {
            Storage::disk('liara')->delete($user->nationalPhoto);
            $inputUser['nationalPhoto'] = null;
        }

        if ($request->hasFile('nationalPhoto')) {
            // $path = $request->file('nationalPhoto')->store('national_photos');

            if ($user->nationalPhoto) {
                Storage::disk('liara')->delete($user->nationalPhoto);
            }
            $path = uploadNationalImageToS3($request->file('nationalPhoto'));

            $inputUser['nationalPhoto'] = $path;
        }


        $request->validate([
            'mobile' => 'unique:users,mobile,' . $user->id
        ]);

        DB::beginTransaction();

        $oldData = $user->toArray();
        $user->update($inputUser);
        $newData = $user->toArray();

        if ($oldData['status'] != $newData['status']) {
            updateUserStatusNotice($user->mobile, $user->status);
        }

        (new User())->logUserModelChanges(Auth::user(), $oldData, $newData);

        $user->load('vehicle');
        $vehicle = $user->vehicle;

        if ($vehicle) {
            $oldData = $vehicle->toArray();
            $data = $request->only(['model', 'type', 'brand', 'pelak', 'color']);

            $vehicle->update([
                'model' => $data['model'],
                'type' => $data['type'],
                'brand' => $data['brand'],
                'pelak' => $data['pelak'],
                'color' => $data['color'],
            ]);
            $newData = $vehicle->toArray();

            // (new Vehicle())->logVehicleModelChanges($user, $oldData, $newData);
        }

        $user->load('vehicle', 'wallet', 'coinWallet');
        DB::commit();

        return $this->sendResponse($user, ".اطلاعات با موفقیت به روز شد");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/vehicle/{vehicleId}/update-access",
     *     summary="Update vehicle",
     *     description="Update the profile and vehicle information of a specific vehicle.",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="vehicleId",
     *         in="path",
     *         description="The ID of the vehicle to update.",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *      @OA\Property(
     *         property="neighborhoodAvailable",
     *         type="array",
     *         description="Required. Array of neighborhood IDs",
     *         @OA\Items(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Property(
     *         property="storesAdminBlock",
     *         type="array",
     *         description="The available stores for the vehicle.",
     *         @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="expire", type="string", format="date")
     *         )
     *     ),
     *     @OA\Property(
     *         property="storesAdminAccess",
     *         type="array",
     *         description="The blocked stores for the vehicle.",
     *         @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="expire", type="string", format="date")
     *         )
     *     ),
     *     @OA\Property(
     *         property="storesBlockedWithUser",
     *         type="array",
     *         description="The blocked stores for the vehicle.",
     *         @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="expire", type="string", format="date")
     *         )
     *     ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="code", type="integer", example=200)
     *         ),
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
     *     ),
     * )
     */
    public function updateVehicleAccess(UpdateVehicleAccessRequest $request, $vehicleId)
    {
        $user = $this->getUserVehicle($vehicleId);


        DB::beginTransaction();

        $user->load('vehicle');
        $vehicle = $user->vehicle;

        $neighborhoodIds = explode(',', $request->get('neighborhoodAvailable', ''));
        $neighborhoodIds = Neighborhood::whereIn('id', $neighborhoodIds)->get();
        $adminId = Auth::id();
        $available = $neighborhoodIds->map(function ($neighborhood) use ($vehicle, $adminId) {
            return [
                'vehicle_id' => $vehicle->id,
                'neighborhood_id' => $neighborhood->id,
                'user_id' => $adminId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        NeighborhoodsAvailable::where('vehicle_id', '=', $vehicle->id)->delete();
        NeighborhoodsAvailable::insert($available->all());

        $req = json_encode($request->get('storesAdminAccess', []));
        $parameters = collect(json_decode($req));

        $storeIds = $parameters->map(function ($parameter) {
            return $parameter->id;
        });
        $storeIds = Store::whereIn('id', $storeIds)->get();
        $adminId = Auth::id();
        $available = $storeIds->map(function ($store) use ($vehicle, $adminId, $parameters) {
            $data = collect($parameters)->firstWhere('id', $store->id);
            $expire = $data->expire;
            try {
                // $expire = date('Y-m-d', $data->expire);
                Carbon::parse($data->expire);
            } catch (\Exception $e) {
                $expire = null;
            }


            return [
                'vehicle_id' => $vehicle->id,
                'store_id' => $store->id,
                'user_id' => $adminId,
                'expire' => $expire,
                'with_admin' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        StoreAvailable::where('vehicle_id', '=', $vehicle->id)->where('with_admin', '=', 1)->delete();
        StoreAvailable::insert($available->all());

        $req = json_encode($request->get('storesAdminBlock', []));
        $parameters = collect(json_decode($req));

        $storeIds = $parameters->map(function ($parameter) {
            return $parameter->id;
        });
        $storeIds = Store::whereIn('id', $storeIds)->get();
        $adminId = Auth::id();
        $blocked = $storeIds->map(function ($store) use ($vehicle, $adminId, $parameters) {
            $data = collect($parameters)->firstWhere('id', $store->id);

            $expire = $data->expire;
            try {
                // $expire = date('Y-m-d', $data->expire);
                Carbon::parse($data->expire);
            } catch (\Exception $e) {
                $expire = null;
            }

            return [
                'vehicle_id' => $vehicle->id,
                'store_id' => $store->id,
                'user_id' => $adminId,
                'expire' => $expire,
                'with_admin' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        StoresBlocked::where('vehicle_id', '=', $vehicle->id)->where('with_admin', '=', '1')->delete();
        StoresBlocked::insert($blocked->all());

        // *****
        $req = json_encode($request->get('storesBlockedWithUser', []));
        $parameters = collect(json_decode($req));

        $storeIds = $parameters->map(function ($parameter) {
            return $parameter->id;
        });
        $storeIds = Store::whereIn('id', $storeIds)->get();
        $userVehicleId = $user->id;
        $blocked = $storeIds->map(function ($store) use ($vehicle, $userVehicleId, $parameters) {
            $data = collect($parameters)->firstWhere('id', $store->id);

            $expire = $data->expire;
            try {
                // $expire = date('Y-m-d', $data->expire);
                Carbon::parse($data->expire);
            } catch (\Exception $e) {
                $expire = null;
            }
            return [
                'vehicle_id' => $vehicle->id,
                'store_id' => $store->id,
                'user_id' => $userVehicleId,
                'expire' => $expire,
                'with_admin' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        StoresBlocked::where('vehicle_id', '=', $vehicle->id)->where('user_id', $userVehicleId)->delete();
        StoresBlocked::insert($blocked->all());
        // ****

        //********* 

        $req = json_encode($request->get('storesBlockedWithStore', []));
        $parameters = collect(json_decode($req));

        $storeIds = $parameters->map(function ($parameter) {
            return $parameter->id;
        });
        $storeIds = Store::whereIn('id', $storeIds)->get();
        $blocked = $storeIds->map(function ($store) use ($vehicle, $parameters) {
            $data = collect($parameters)->firstWhere('id', $store->id);

            $expire = $data->expire;
            try {
                // $expire = date('Y-m-d', $data->expire);
                Carbon::parse($data->expire);
            } catch (\Exception $e) {
                $expire = null;
            }
            return [
                'vehicle_id' => $vehicle->id,
                'store_id' => $store->id,
                'user_id' => $data->user_id,
                'expire' => $expire,
                'with_admin' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
        });

        StoresBlocked::where('vehicle_id', '=', $vehicle->id)->where('with_admin', '=', '0')->where('user_id', '<>', $userVehicleId)->delete();
        StoresBlocked::insert($blocked->all());
        // ******* 
        $user->load('vehicle', 'wallet', 'coinWallet');
        DB::commit();

        return $this->sendResponse($user, ".اطلاعات با موفقیت به روز شد");
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/vehicle/{vehicleId}",
     *     summary="Delete vehicle by ID",
     *     description="Deletes a single vehicle by ID",
     *     operationId="deleteVehicleById",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="vehicleId",
     *         in="path",
     *         description="ID of vehicle to delete",
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
     *         description="vehicle not found"
     *     )
     * )
     */
    public function deleteVehicle(Request $request, $vehicleId)
    {
        $user = $this->getUserVehicle($vehicleId);

        Log::store(LogUserTypesEnum::ADMIN, Auth::id(), LogModelsEnum::VEHICLE, LogActionsEnum::DELETE, $user);

        return $user->delete();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/roles",
     *     summary="Get all roles",
     *     description="Returns all roles",
     *     operationId="getRoles",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="name", type="string", example="admin"),
     *                 @OA\Property(property="description", type="string", example="Administrator role"),
     *                 @OA\Property(property="display_name", type="string", example="Admin"),
     *             ),
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
     * )
     */
    public function getRoles(Request $request)
    {
        $roles = collect(Role::select('id', 'name', 'display_name', 'description')->get());
        // $roles->push(['name' => 'client', 'display_name' => 'مشتری', 'description' => 'پیک یا مغازه دار ها']);
        return $this->sendResponse($roles, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/permissions",
     *     summary="Get all permissions",
     *     description="Returns all permissions",
     *     operationId="getPermissions",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401),
     *         ),
     *     ),
     * )
     */
    public function getPermissions(Request $request)
    {
        $permissions = collect(Permission::select('id', 'name', 'display_name', 'description')->get());
        return $this->sendResponse($permissions, Lang::get('http-statuses.200'));
    }


    /**
     * @OA\Post(
     *     path="/api/v1/admin/role",
     *     summary="Add new role",
     *     description="Add new role",
     *     tags={"Admin"},
     *     security={ {"sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="code", type="integer", example=401),
     *         ),
     *     ),
     * )
     */
    public function addNewRole(Request $request)
    {
        // Create the role
        $role = Role::create(['name' => $request->name]);

        $permissions = Permission::whereIn('id', $request->get('permissions', []));
        // Assign permissions to the role
        foreach ($permissions as $permissionName) {
            $permission = Permission::first(['name' => $permissionName]);
            $role->givePermissionTo($permission);
        }
        return $this->sendResponse($role, Lang::get('http-statuses.200'));
    }

    public function updatePassword(Request $request)
    {
        $user = User::findOrFail($request->get('user_id', 0));
        if ($request->password)
            $user->password = bcrypt($request->password);
        $user->save();
        if ($request->get('sendNotice', false)) {
            updatePassNotice($request->get('mobile'), $request->get('password'));
        }


        return $this->sendResponse($user, ".اطلاعات با موفقیت به روز شد");
    }
}