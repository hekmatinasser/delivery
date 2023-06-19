<?php

namespace App\Http\Requests\CoinWallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     title="StoreCoinWalletTransactionRequest",
 *     description="Store Coin Wallet Transaction Request body data",
 *     type="object",
 *     required={
 *         "action",
 *         "coins",
 *         "reason_code"
 *     },
 *     @OA\Property(
 *         property="action",
 *         description="Action to perform on the coin wallet balance",
 *         type="string",
 *         enum={"increase", "decrease"}
 *     ),
 *     @OA\Property(
 *         property="coins",
 *         description="Amount of coins to perform the action on",
 *         type="number",
 *         format="float"
 *     ),
 *     @OA\Property(
 *         property="reason_code",
 *         description="Reason code for the coin wallet transaction",
 *         type="number",
 *         format="integer"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="Description of the coin wallet transaction",
 *         type="string"
 *     )
 * )
 */
class StoreCoinWalletTransactionRequest extends FormRequest
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
            // 'user_id' => ['nullable', 'exists:users,id'],
            'action' => ['required', 'in:increase,decrease'],
            'coins' => ['required', 'numeric'],
            'reason_code' => ['required', 'numeric', 'exists:coin_wallet_transaction_reasons,code'],
            'description' => ['nullable', 'string'],
            // 'image' => ['nullable', 'file', 'mimes:jpg, png, jpeg, webp']
        ];
    }
}
