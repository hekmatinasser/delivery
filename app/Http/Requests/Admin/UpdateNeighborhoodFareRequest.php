<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="UpdateNeighborhoodFareRequest",
 *     title="Store Neighborhood Fare Request",
 *     description="Request body for storing a new neighborhood fare",
 *          required={
 *              "neighborhood_id",
 *              "origin",
 *              "destination",
 *              "fare"
 *          },
 *          @OA\Property(
 *              property="neighborhood_id",
 *              type="integer",
 *              description="The ID of the neighborhood fare to update",
 *          ),
 *          @OA\Property(
 *              property="origin",
 *              type="integer",
 *              description="The ID of the origin neighborhood",
 *          ),
 *          @OA\Property(
 *              property="destination",
 *              type="integer",
 *              description="The ID of the destination neighborhood",
 *          ),
 *          @OA\Property(
 *              property="fare",
 *              type="integer",
 *              description="The new fare for the neighborhood",
 *          ),
 * )
 *
 */
class UpdateNeighborhoodFareRequest extends FormRequest
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
            'neighborhood_id' => 'required|integer|exists:inter_neighborhood_fares,id',
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
            'neighborhood_id' => 'شناسه',
            'origin' => 'مبدا',
            'destination' => 'مقصد',
            'fare' => 'کرایه',
        ];
    }
}
