<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="UpdateProfileRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", maxLength=70),
 *     @OA\Property(property="family", type="string", maxLength=70),
 *     @OA\Property(property="mobile", type="string", format="mobile", example="09123456789"),
 *     @OA\Property(property="nationalCode", type="string", format="nationalCode", example="0123456789"),
 *     @OA\Property(property="nationalPhoto", type="string", format="binary", description="The user's national photo image file (JPEG or PNG format, max size 15MB, min dimensions 100x100."),
 *     @OA\Property(property="address", type="string", maxLength=255),
 *     @OA\Property(property="postCode", type="string", format="postCode", example="1234567890"),
 *     @OA\Property(property="phone", type="string", format="phone", example="1234567890"),
 * )
 */

class UpdateProfileRequest extends FormRequest
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
            'name' => 'nullable|max:70',
            'family' => 'nullable|max:70',
            'mobile' => 'Required|regex:/(09)[0-9]{9}/|digits:11|numeric|exists:users',
            'nationalCode' => 'nullable|digits:10|numeric',
            'nationalPhoto' => 'nullable|mimes:jpeg,png|max:15360|dimensions:min_width=100,min_height=100',
            'address' => 'nullable|max:255',
            'postCode' => 'nullable|digits:10|numeric',
            'phone' => 'nullable|numeric',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nationalPhoto' => 'تصویر کارت ملی',
            'postCode' => 'کد پستی',
        ];
    }
}
