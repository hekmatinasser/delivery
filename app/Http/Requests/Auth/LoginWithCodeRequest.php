<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LoginWithCodeRequest",
 *     title="Login With Code Request",
 *     description="Pass user activation code and mobile number",
 *     @OA\Property(property="code", type="string", format="code", example="1234"),
 *     @OA\Property(property="mobile", type="string", format="mobile", example="09123456789")
 * )
 */
class LoginWithCodeRequest extends FormRequest
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
            'code' => 'required|digits:4|numeric',
            'type'=>'nullable'
        ];
    }
}
