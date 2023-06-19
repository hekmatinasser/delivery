<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="BuyCoinRequest",
 *     title="BuyCoinRequest",
 *     description="The amount of coin to buy",
 *     type="object",
 *     required={"action"},
 *     @OA\Property(
 *         property="amount",
 *         description="The amount to add or subtract from the wallet balance",
 *         type="number",
 *         format="float"
 *     )
 * )
 */
class BuyCoinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric']
        ];
    }
}
