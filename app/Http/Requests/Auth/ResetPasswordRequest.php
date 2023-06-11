<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="ResetPasswordRequest",
 *     title="Reset Password Request",
 *     description="Pass verification code and new password",
 *     @OA\Property(property="code", type="string", example="1234"),
 *     @OA\Property(property="password", type="string", format="password", example="newpassword")
 * )
 */
class ResetPasswordRequest extends FormRequest
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
            'password' => 'required|min:5',
            'code' => 'required|digits:4|numeric'
        ];
    }
}
