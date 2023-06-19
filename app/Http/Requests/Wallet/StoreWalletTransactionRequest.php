<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="StoreWalletTransactionRequest",
 *     title="StoreWalletTransactionRequest",
 *     description="Store wallet transaction request body data",
 *     type="object",
 *     required={"action", "amount", "reason_code"},
 *     @OA\Property(
 *         property="action",
 *         description="The action to perform on the wallet balance",
 *         type="string",
 *         enum={"increase", "decrease"}
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         description="The amount to add or subtract from the wallet balance",
 *         type="number",
 *         format="float"
 *     ),
 *     @OA\Property(
 *         property="reason_code",
 *         description="The reason code for the wallet transaction",
 *         type="number",
 *         format="integer"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="An optional description for the wallet transaction",
 *         type="string"
 *     )
 * )
 */
class StoreWalletTransactionRequest extends FormRequest
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
            'amount' => ['required', 'numeric','min:0'],
            'reason_code' => ['required', 'numeric', 'exists:wallet_transaction_reasons,code'],
            'description' => ['nullable', 'string'],
            // 'image' => ['nullable', 'file', 'mimes:jpg, png, jpeg, webp']
        ];
    }
}
