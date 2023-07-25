<?php

namespace App\Http\Controllers\API;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginWithCodeRequest;
use App\Http\Requests\Auth\LoginWithPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyRequest;
use App\Models\Log;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    /**
     *  @OA\Post(
     *     path="/api/v1/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Registers a new user and sends a verification code via SMS",
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", @OA\Property(property="is_new", type="bolean", example="true")),
     *             @OA\Property(property="message", type="string", example="Authentication code sent to your mobile.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $user = User::findByMobile($request->mobile);
        $isNew = true;
        if (!$user) {
            $user = User::create($request->all());
            Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::REGISTER, LogActionsEnum::REQUEST);
        } else {
            Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::LOGIN, LogActionsEnum::REQUEST);
            $isNew = false;
        }

        try {
            $code = (new VerifyCode())->createNewCode($user->mobile);
            verifySMS($user->mobile, $code);
        } catch (Exception $e) {
            return $this->sendError(Lang::get('http-statuses.500'), $e->getMessage(), 500);
        }

        return $this->sendResponse(['is_new' => $isNew], Lang::get('auth.code'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/verify",
     *     summary="Complete user registration",
     *     description="Complete user registration with activation code",
     *     tags={"Authentication"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/VerifyRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                  @OA\Property(property="token", type="string", example="hash-value"),
     *                  @OA\Property(property="name", type="string", example="John"),
     *                  @OA\Property(property="family", type="string", example="Doe"),
     *             ),
     *             @OA\Property(property="message", type="string", example="Completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error Response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function verify(VerifyRequest $request)
    {
        // TODO CHECK if validation Logger is available
        $user = (new User())->getUserByActivationCode($request->code);
        if (!$user || $user->mobile != $request->mobile) {
            if (!$user) {
                $user = User::findByMobile($request->mobile);
                Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::REGISTER, LogActionsEnum::FAILED, Lang::get('auth.code_failed'));
            }
            return $this->sendError(Lang::get('auth.code_failed'), [], 400);
        }

        VerifyCode::where('mobile', $user->mobile)->delete();

        $user->password = bcrypt($request->password);
        $user->wallet()->create();
        $user->coinWallet()->create();
        $user->status = 1;
        $user->save();

        $data['token'] =  $user->createToken('client')->plainTextToken;
        $data['name'] =  $user->name;
        $data['family'] =  $user->family;

        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::REGISTER, LogActionsEnum::SUCCESS);
        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::LOGIN, LogActionsEnum::SUCCESS);
        return $this->sendResponse([$data], Lang::get('auth.profile_created'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="User login",
     *     description="Login user with mobile number",
     *     tags={"Authentication"},
     *
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification code sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="string",example=""),
     *             @OA\Property(property="message", type="string", example="Authentication code sent to your mobile.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        // TODO CHECK if validation Logger is available
        $user = User::findByMobile($request->mobile);
        if (!$user) {
            return $this->sendError(Lang::get('auth.failed'), '', 401);
        }

        try {
            $code = (new VerifyCode())->createNewCode($user->mobile);
            verifySMS($user->mobile, $code);
        } catch (Exception $e) {
            return $this->sendError(Lang::get('http-statuses.500'), $e->getMessage(), 500);
        }

        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::LOGIN, LogActionsEnum::REQUEST);
        return $this->sendResponse('', Lang::get('auth.code'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login/code",
     *     summary="Login with activation code and mobile number",
     *     description="Logs in a user with an activation code and mobile number",
     *     tags={"Authentication"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/LoginWithCodeRequest")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful login response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     example="hash-value"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John"
     *                 ),
     *                 @OA\Property(
     *                     property="family",
     *                     type="string",
     *                     example="Doe"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Login successful"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error Response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *         )
     *     )
     * )
     */
    public function loginWithCode(LoginWithCodeRequest $request)
    {
        // TODO CHECK if validation Logger is available
        $user = (new User())->getUserByActivationCode($request->code);
        $type = $request->get('type', 0);

        if (!$user || $user->mobile != $request->mobile) {
            if (!$user) {
                $user = User::findByMobile($request->mobile);
                Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::LOGIN, LogActionsEnum::FAILED, Lang::get('auth.code_failed'));
            }
            return $this->sendError(Lang::get('auth.code_failed'), [], 400);
        }

        VerifyCode::where('mobile', $user->mobile)->delete();

        if($type == 1 && $user->userType != "1"){
            return $this->sendError(Lang::get('auth.failed'), '', 403);
        }
        $user->load('abilites');
        $pluck = collect($user->abilites)->pluck('name');
        $abilities = $pluck->all();
        $tokenName = 'client';
        if (count($abilities)) {
            $tokenName = 'admin';
        }
        $success['token'] =  $user->createToken($tokenName, $abilities)->plainTextToken;
        $success['name'] =  $user->name;
        $success['family'] =  $user->family;

        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::LOGIN, LogActionsEnum::SUCCESS);
        // TODO CLEAR this LOGGER after SECCUSS LOGIN
        return $this->sendResponse($success, Lang::get('auth.done'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login/password",
     *     summary="Login with mobile number and password",
     *     description="Logs in a user with a mobile number and password",
     *     tags={"Authentication"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/LoginWithPasswordRequest")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful login response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     example="hash-value"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John"
     *                 ),
     *                 @OA\Property(
     *                     property="family",
     *                     type="string",
     *                     example="Doe"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Login successful"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized login response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorised.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *         )
     *     )
     * )
     */
    public function loginWithPassword(LoginWithPasswordRequest $request)
    {
        // TODO CHECK if validation Logger is available
        $user = (new User())->findByMobile($request->mobile);
        $type = $request->get('type', 0);

        if (Auth::attempt(['mobile' => $request->mobile, 'password' => $request->password])) {

            if($type == 1 && $user->userType != "1"){
                return $this->sendError(Lang::get('auth.failed'), '', 403);
            }

            $user = User::find(Auth::id());
            $user->load('abilites');
            $pluck = collect($user->abilites)->pluck('name');
            $abilities = $pluck->all();
            $tokenName = 'client';
            if (count($abilities)) {
                $tokenName = 'admin';
            }
            $success['token'] =  $user->createToken($tokenName, $abilities)->plainTextToken;
            $success['name'] =  $user->name;
            $success['family'] =  $user->family;


            Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::LOGIN, LogActionsEnum::SUCCESS);
            // TODO CLEAR this LOGGER after SECCUSS LOGIN
            return $this->sendResponse($success, Lang::get('auth.done'));
        } else {
            if ($user) {
                Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::LOGIN, LogActionsEnum::FAILED, Lang::get('auth.failed'));
            }
            return $this->sendError(Lang::get('auth.failed'), '', 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/forgot-password",
     *     summary="Send verification code to reset password",
     *     description="Sends a verification code to the user's mobile number to reset their password",
     *     tags={"Authentication"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/ForgotPasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="string", example=""),
     *             @OA\Property(property="message", type="string", example="Verification code has been sent.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error Response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *         )
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // TODO CHECK if validation Logger is available
        $user = User::where('mobile', $request->mobile)->first();
        if ($user) {
            try {
                $code = (new VerifyCode())->createNewCode($user->mobile);
                verifySMS($user->mobile, $code);
            } catch (Exception $e) {
                return $this->sendError(Lang::get('http-statuses.500'), $e->getMessage(), 500);
            }

            Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::FORGOT_PASSWORD, LogActionsEnum::SUCCESS);
            // TODO CLEAR this LOGGER after SECCUSS ACTION
            return $this->sendResponse('', Lang::get('auth.code'));
        } else
            return $this->sendError(Lang::get('auth.failed'), "", 400);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/reset-password",
     *     summary="Reset user's password",
     *     description="Resets the user's password using the verification code sent to their mobile number",
     *     tags={"Authentication"},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="string", example=""),
     *             @OA\Property(property="message", type="string", example="password is updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error Response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error response",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *         )
     *     )
     * )
     */
    public function restPassword(ResetPasswordRequest $request)
    {
        $user = (new User())->getUserByActivationCode($request->code);
        if (!$user) {
            return $this->sendError(Lang::get('auth.code_failed'), [], 400);
        }

        VerifyCode::where('mobile', $user->mobile)->delete();

        // TODO CHECK if validation Logger is available
        $user->password = bcrypt($request->password);
        $user->save();

        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::RESET_PASSWORD, LogActionsEnum::SUCCESS);
        // TODO CLEAR this LOGGER after SECCUSS ACTION
        return $this->sendResponse('', Lang::get('passwords.reset'));
    }

    public function logout(Request $request)
    {
        $userId = Auth::user()->id;
        $request->user()->token()->revoke();
        Log::store(LogUserTypesEnum::USER, $userId, LogModelsEnum::LOGOUT, LogActionsEnum::SUCCESS);
        return $this->sendResponse("", 'خروج از حساب کاربری با موفقیت انجام شد');
    }
}
