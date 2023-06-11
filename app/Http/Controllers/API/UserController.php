<?php

namespace App\Http\Controllers\API;

use App\Enums\LogActionsEnum;
use App\Enums\LogModelsEnum;
use App\Enums\LogUserTypesEnum;
use App\Models\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/user",
     *     summary="Get user profile",
     *     description="Returns the authenticated user's profile information",
     *     tags={"User"},
     *     security={ {"sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/User"),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse"),
     *     ),
     * )
     */
    public function profile(Request $request)
    {
        $user = Auth::user();
        $user = User::find($user->id);
        $user->load(['wallet', 'coinWallet']);

        Log::store(LogUserTypesEnum::USER, $user->id, LogModelsEnum::USER, LogActionsEnum::VIEW_DETAILS);
        return $this->sendResponse($user, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user",
     *     summary="Update user profile",
     *     description="Update the authenticated user's profile information",
     *     tags={"User"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *             required=true,
     *             description="The user's profile information to update",
     *             @OA\MediaType(
     *                      mediaType="multipart/form-data",
     *                      @OA\Schema(
     *                      @OA\Property(property="name", type="string", maxLength=70),
     *                      @OA\Property(property="family", type="string", maxLength=70),
     *                      @OA\Property(property="mobile", type="string", format="mobile", example="09123456789"),
     *                      @OA\Property(property="nationalCode", type="string", format="nationalCode", example="0123456789"),
     *                      @OA\Property(property="nationalPhoto", type="string", format="binary", description="The user's national photo image file (JPEG or PNG format, max size 15MB, min dimensions 100x100, max dimensions 1000x1000)."),
     *                      @OA\Property(property="address", type="string", maxLength=255),
     *                      @OA\Property(property="postCode", type="string", format="postCode", example="1234567890"),
     *                      @OA\Property(property="phone", type="string", format="phone", example="1234567890")
     *                 )
     *             )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The updated user profile",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The user profile was successfully updated and is awaiting admin approval."),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
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
    public function update(UpdateProfileRequest $request)
    {
        if ($request->nationalCode)
            if (!checkNationalcode($request->nationalCode))
                return $this->sendError('national Code Not Valid.', ['error' => ['nationalCode' => 'کد ملی معتبر نمی باشد']], 422);

        $input = $request->all();
        $user = User::find(Auth::id());

        if ($request->hasFile('nationalPhoto')) {
            if ($user->nationalPhoto) {
                Storage::delete($user->nationalPhoto);
            }
            $path = $request->file('nationalPhoto')->store('natinal_photos');

            $input['nationalPhoto'] = $path;
        }

        $input['status'] = 0;

        $oldData = $user->toArray();
        $user->update($input);
        $newData = $user->toArray();

        (new User())->logUserModelChanges($user, $oldData, $newData);

        return $this->sendResponse($newData, ".بروزرسانی با موفقیت انجام شد\\nمنتظر تایید ادمین باشید");
    }
}
