<?php

namespace App\Http\Controllers\API;

use App\Models\Log;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    public function profile(Request $request)
    {
        $user = Auth::user();
        $success['name'] =  $user->name;
        $success['family'] =  $user->family;
        $success['mobile'] =  $user->mobile;
        $success['nationalCode'] =  $user->nationalCode;
        $success['email'] =  $user->email;

        Log::store(0, $user->id, 'User', 10);
        return $this->sendResponse($success, 'Your Profile Updated');
    }


    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|max:255',
            'family' => 'nullable|max:255',
            'mobile' => 'nullable|regex:/(09)[0-9]{9}/|digits:11|numeric',
            'nationalCode' => 'nullable|digits:10|numeric',
            'email' => 'email',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if($request->nationalCode)
            if(!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['error'=>'کد ملی معتبر نمی باشد']);

        $input = $request->all();
        if($request->password)
            $input['password'] = bcrypt($input['password']);
        else
            unset($input['password']);

        $input['status'] = 0;

        $user = Auth::user();
        // $user = $user->update($input);
        $success['name'] =  $user->name;
        $success['family'] =  $user->family;

        Log::store(0, $user->id, 'User', 11);

        return $this->sendResponse($success, 'بروزرسانی با موفقیت انجام شد');
    }


}
