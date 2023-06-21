<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="UpdateCoinSettingRequest",
 *     type="object",
 *     required={
 *         "id",
 *         "shop_coin_fee",
 *         "vehicle_coin_fee",
 *         "shop_coin_rial_fee",
 *         "motor_coin_rial_fee"
 *     },
 *     @OA\Property(
 *         property="id",
 *         type="number",
 *         format="integer",
 *         description="The setting coin id"
 *     ),
 *     @OA\Property(
 *         property="shop_coin_fee",
 *         type="number",
 *         format="integer",
 *         minimum="0",
 *         description="The new fee for shops in coins"
 *     ),
 *     @OA\Property(
 *         property="vehicle_coin_fee",
 *         type="number",
 *         format="integer",
 *         minimum="0",
 *         description="The new fee for vehicles in coins"
 *     ),
 *     @OA\Property(
 *         property="shop_coin_rial_fee",
 *         type="number",
 *         format="integer",
 *         minimum="0",
 *         description="The new fee for shops in rials"
 *     ),
 *     @OA\Property(
 *         property="motor_coin_rial_fee",
 *         type="number",
 *         format="integer",
 *         minimum="0",
 *         description="The new fee for motorbikes in rials"
 *     ),
 * )
 */
class UpdateCoinSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->tokenCan('coin-setting-modify');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|numeric|exists:coin_settings,id',
            'shop_coin_fee' => 'required|numeric|min:0',
            'vehicle_coin_fee' => 'required|numeric|min:0',
            'shop_coin_rial_fee' => 'required|numeric|min:0',
            'motor_coin_rial_fee' => 'required|numeric|min:0',
        ];
    }
}
