<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LoginWithPasswordRequest",
 *     title="Login With PasswordRequest",
 *     description="Pass user mobile number and password",
 *     @OA\Property(property="mobile", type="string", format="mobile", example="09123456789"),
 *     @OA\Property(property="password", type="string", format="password", example="MyNewPassword123")
 * )
 */
class LoginWithPasswordRequest extends FormRequest
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
            'mobile' => 'required|regex:/(09)[0-9]{9}/|digits:11|numeric',
            'password' => 'required|min:5',
            'type' => 'nullable',
        ];
    }
}
