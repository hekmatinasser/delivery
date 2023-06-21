<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="StoreNeighborhoodFareRequest",
 *     title="Store Neighborhood Fare Request",
 *     description="Request body for storing a new neighborhood fare",
 *     @OA\Property(
 *         property="destination",
 *         type="integer",
 *         description="The ID of the destination neighborhood",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="origin",
 *         type="integer",
 *         description="The ID of the origin neighborhood",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="fare",
 *         type="integer",
 *         description="The fare for the neighborhood IRT",
 *         example=12000
 *     ),
 * )
 *
 */
class StoreNeighborhoodFareRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->tokenCan('neighborhood-modify');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'origin' => 'required|integer|exists:neighborhoods,id',
            'destination' => 'required|integer|exists:neighborhoods,id|different:origin',
            'fare' => 'required|integer|min:0',
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
            'origin' => 'مبدا',
            'destination' => 'مقصد',
            'fare' => 'کرایه',
        ];
    }
}
