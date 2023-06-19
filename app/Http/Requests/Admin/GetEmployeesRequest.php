<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="GetEmployeesRequest",
 *     title="Get Employees Request",
 *     description="Request body data for retrieving a paginated list of employees",
 *     type="object",
 *     @OA\Property(
 *         property="page",
 *         type="integer",
 *         description="The page number to retrieve, defaults to 1",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         description="The number of results per page, must be one of: 5, 10, 15, 20",
 *         example=10,
 *     ),
 * )
 */
class GetEmployeesRequest extends FormRequest
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
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|in:5,10,15,20',
        ];
    }
}
