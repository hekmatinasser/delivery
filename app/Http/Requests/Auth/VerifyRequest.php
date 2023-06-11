<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="VerifyRequest",
 *     title="Compleate RegistrationRequest Request",
 *     description="Pass user mobile number and activation code",
 *     required={"mobile", "code", "password"},
 *     @OA\Property(property="mobile", type="string", example="09123456789"),
 *     @OA\Property(property="code", type="string", example="1234"),
 *     @OA\Property(property="password", type="string", example="MyNewPassword123")
 * )
 */
class VerifyRequest extends FormRequest
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
            'code' => 'required|digits:4|numeric'
        ];
    }
}
