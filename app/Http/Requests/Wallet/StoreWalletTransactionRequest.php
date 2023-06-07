<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class StoreWalletTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'action' => ['required', 'in:increase,decrease'],
            'amount' => ['required', 'numeric'],
            'reason_code' => ['required', 'numeric', 'exists:wallet_transaction_reasons,code'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'mimes:jpg, png, jpeg, webp']
        ];
    }
}
