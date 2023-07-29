<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

/**
 * @OA\Schema(
 *     schema="CreateEmployeeRequest",
 *     title="Create Employee Request",
 *     description="Create Employee Request body data",
 *     type="object",
 *     required={
 *         "mobile"
 *     },
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Employee's name",
 *         example="John"
 *     ),
 *     @OA\Property(
 *         property="family",
 *         type="string",
 *         description="Employee's family name",
 *         example="Doe"
 *     ),
 *     @OA\Property(
 *         property="employee_code",
 *         type="string",
 *         description="Employee's code",
 *         example="Doe1523"
 *     ),
 *     @OA\Property(
 *         property="mobile",
 *         type="string",
 *         description="Employee's mobile number",
 *         example="09123456789"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="Employee's password",
 *         example="newPassword",
 *     ),
 *     @OA\Property(
 *         property="nationalCode",
 *         type="string",
 *         description="Employee's national code",
 *         example="0012345678"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         description="Employee's address",
 *         example="123 Main St, Anytown, USA"
 *     ),
 *     @OA\Property(
 *         property="postCode",
 *         type="string",
 *         description="Employee's postal code",
 *         example="1234567890"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         description="Employee's phone number",
 *         example="1234567890"
 *     ),
 *     @OA\Property(
 *         property="role",
 *         type="string",
 *         description="Employee's role",
 *         example="admin"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="integer",
 *         description="Employee's status :: 1 => active, 0 => inactive, -1 => suspended, -2 => blocked",
 *         example="0"
 *     ),
 * )
 */
class CreateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->tokenCan('user-modify');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'Required|max:70',
            'employee_code' => 'Required|max:70',
            'family' => 'Required|max:70',
            'mobile' => 'Required|regex:/(09)[0-9]{9}/|digits:11|numeric|unique:users,mobile',
            'password' => 'nullable|min:5',
            'nationalCode' => 'nullable|digits:10|numeric',
            'nationalPhoto' => 'nullable|mimes:jpeg,png|max:15360|dimensions:min_width=100,min_height=100',
            'address' => 'nullable|max:255',
            'postCode' => 'nullable|digits:10|numeric',
            'phone' => 'nullable|numeric',
            'role' => 'Required',
            'status' => 'nullable|in:1,0,-1,-2'
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
            'phone' => 'تلفن ثابت',
            'lat' => 'طول جغرافیایی',
            'lang' => 'عرض جغرافیایی',
            'status' => 'وضعیت',
            'employee_code' => 'کد کارمند'
        ];
    }
}
