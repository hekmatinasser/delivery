<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCoinSettingRequest;
use App\Models\CoinSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CoinSettingController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/v1/coin-setting",
     *     summary="Get the coin setting",
     *     description="Returns the current coin setting",
     *     tags={"Coin Setting"},
     *     security={ {"sanctum": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized access",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     * )
     */
    public function getCoinSetting()
    {
        $coinSetting = CoinSetting::find(1);

        return $this->sendResponse($coinSetting, Lang::get('http-statuses.200'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/coin-setting",
     *     summary="Save the coin setting",
     *     description="Updates or creates the coin setting",
     *     tags={"Coin Setting"},
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         description="The new coin setting",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateCoinSettingRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"message": "The coin setting has been saved successfully"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
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
     *     )
     * )
     */
    public function saveCoinSetting(UpdateCoinSettingRequest $request)
    {
        CoinSetting::updateOrCreate(
            [
                'id' => '1',
            ],
            [
                'shop_coin_fee' => $request->shop_coin_fee,
                'vehicle_coin_fee' => $request->vehicle_coin_fee,
                'shop_coin_rial_fee' => $request->shop_coin_rial_fee,
                'motor_coin_rial_fee' => $request->motor_coin_rial_fee,
            ]
        );

        return $this->sendResponse('', Lang::get('http-statuses.200'));
    }
}
