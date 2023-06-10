<?php

namespace App\Http\Controllers\API;

use App\Models\Log;
use App\Models\user\UserVerify;
use App\Models\VerifyCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'family' => 'required|max:255',
            'mobile' => 'required|regex:/(09)[0-9]{9}/|digits:11|numeric|unique:users',
            'nationalCode' => 'required|digits:10|numeric',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (!checkNationalcode($request->nationalCode))
            return $this->sendError('national Code Not Valid.', ['error' => 'کد ملی معتبر نمی باشد']);

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['status'] = 0;
        $input['unValidCodeCount'] = 0;
        $user = User::create($input);
        $user->wallet()->create();
        $user->coinWallet()->create();

        VerifyCode::where('mobile', $user->mobile)->delete();
        $code = rand(1000, 9999);
        VerifyCode::create([
            'code' => $code,
            'mobile' => $user->mobile,
        ]);
        verifySMS($user->mobile, $code);

        Log::store(0, $user->id, 'Register', 5);
        return $this->sendResponse('', 'کد تایید برای شما ارسال شد');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/(09)[0-9]{9}/|digits:11|numeric',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (Auth::attempt(['mobile' => $request->mobile, 'password' => $request->password])) {
            $user = Auth::user();
            Log::store(0, $user->id, 'Login', 5);
            // $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['name'] =  $user->name;
            $success['family'] =  $user->family;
            return $this->sendResponse($success, 'ورود با موفقیت انجام شد');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }

    public function forgetPass(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|regex:/(09)[0-9]{9}/|digits:11|numeric',
        ]);
        if ($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());

        $user = User::where('mobile', $request->mobile)->first();
        if ($user) {
            $last = User::where('updated_at', '>=', Carbon::now()->subSecond(60)->toDateTimeString())
                ->where('user_id', $user->id,)->first();
            if ($last) {
                return $this->sendResponse('', 'رمز عبور برای شما ارسال شده است');
            } else {
                Log::store(0, $user->id, 'ResetPass', 5);
                $pass = rand(1000, 9999);
                $user->update(['password' => bcrypt($pass)]);
                // smsForgetPass($user, $pass);
                return $this->sendResponse('', 'رمز عبور برای شما ارسال شد');
            }
        } else
            return $this->sendError('Mobile Error.', 'برای شماره تلفن ارسال شده حساب کاربری یافت نشد');
    }

    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|max:4',
        ]);
        if ($validator->fails())
            return $this->sendError('Validation Error.', $validator->errors());

        $verify = VerifyCode::where('code', $request->code)->first();
        if (!$verify) {
            return response()->json(['status' => -1, 'data' => 'invalidCode'], 401);
        }

        if (User::where('mobile', $verify->mobile)->first())
            $user = User::where('mobile', $verify->mobile)->first();
        else
            return $this->sendError('Mobile Error.', 'برای شماره تلفن ارسال شده حساب کاربری یافت نشد');

        $verify->delete();
        //        $user->update(['status' => 1]);

        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
        $success['family'] =  $user->family;

        return $this->sendResponse($success, 'منتظر تایید ادمین باشید');
    }

    public function logout(Request $request)
    {
        Log::store(0, Auth::user()->id, 'Logout', 5);
        $request->user()->token()->revoke();
        return $this->sendResponse("", 'خروج از حساب کاربری با موفقیت انجام شد');
    }
}
