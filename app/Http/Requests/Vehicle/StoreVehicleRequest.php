<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="StoreVehicleRequest",
 *     title="Store Vehicle Request",
 *     description="Request body for storing a new vehicle",
 *     type="object",
 *     required={
 *         "type",
 *         "brand",
 *         "pelak",
 *         "color",
 *         "model"
 *     },
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="The type of the vehicle (MOTOR or CAR)",
 *         enum={"MOTOR", "CAR"}
 *     ),
 *     @OA\Property(
 *         property="brand",
 *         type="string",
 *         description="The brand of the vehicle",
 *         maxLength=150
 *     ),
 *     @OA\Property(
 *         property="pelak",
 *         type="string",
 *         description="The pelak of the vehicle",
 *         maxLength=50
 *     ),
 *     @OA\Property(
 *         property="color",
 *         type="string",
 *         description="The color of the vehicle",
 *         maxLength=50
 *     ),
 *     @OA\Property(
 *         property="model",
 *         type="string",
 *         description="The model of the vehicle",
 *         maxLength=150
 *     )
 * )
 */
class StoreVehicleRequest extends FormRequest
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
            'type' => 'required|in:MOTOR,CAR',
            'brand' => 'required|max:150',
            'pelak' => 'required|max:50',
            'color' => 'required|max:50',
            'model' => 'required|max:150',
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
            'type' => 'نوع وسیله نقلیه',
            'brand' => 'برند',
            'pelak' => 'شماره پلاک',
            'color' => 'رنگ وسیله نقلیه',
            'model' => 'سال ساخت',
        ];
    }
}
