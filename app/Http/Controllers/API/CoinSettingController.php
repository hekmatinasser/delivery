<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CoinSetting;
use Illuminate\Http\Request;

class CoinSettingController extends Controller
{
    public function getCoinSetting()
    {
        $coinSetting = CoinSetting::find(1);
        return response()->json([
            'status' => true,
            'coinSettign' => $coinSetting,
        ], 200);
    }

    public function saveCoinSetting(Request $request)
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

        return response()->json([
            'status' => true,
            'message' => 'Coin Setting Saved Successfully!',
        ]);
    }
}
