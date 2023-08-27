<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="UpdateTripRequest",
 *     @OA\Property(property="store_id", type="integer", example=1),
 *     @OA\Property(property="vehicle_id", type="integer", example=2),
 *     @OA\Property(property="origin_id", type="integer", example=1),
 *     @OA\Property(property="destination_id", type="integer", example=2),
 *     @OA\Property(property="shipment_prepare_time", type="string", format="date-time", example="2022-01-01T00:00:00Z"),
 *     @OA\Property(property="deliver_time", type="string", format="date-time", example="2022-01-02T00:00:00Z"),
 *     @OA\Property(property="arrive_time", type="string", format="date-time", example="2022-01-03T00:00:00Z"),
 *     @OA\Property(property="trip_rial_fare", type="integer", example=100),
 *     @OA\Property(property="status", type="integer", example=1,description="1=> سفارش ثبت شد,2=> پیک سفارش را پذیرفت,3=> پیک در انتظار دریافت بسته,4=> پیک در مسیر مشتری,5=> بسته تحویل شد,6=> لغو شده"),
 *     @OA\Property(property="customer_name", type="string", example="John Doe"),
 *     @OA\Property(property="customer_phone", type="string", example="1234567890"),
 *     @OA\Property(property="description", type="string", example="Some description"),
 *     @OA\Property(property="manager_description", type="string", example="Some manager description")
 * )
 */
class UpdateTripRequest extends FormRequest
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
            'store_id' => 'nullable|numeric|exists:store,id',
            'vehicle_id' => 'nullable|numeric|exists:vehicle,id',
            'origin_id' => 'nullable|numeric|exists:neighborhoods,id',
            'destination_id' => 'nullable|numeric|exists:neighborhoods,id',
            'shipment_prepare_time' => 'nullable|date',
            'deliver_time' => 'nullable|date',
            'arrive_time' => 'nullable|date',
            'trip_rial_fare' => 'nullable|numeric',
            'status' => 'nullable|in:1,2,3,4,5,6',
            'customer_name' => 'nullable',
            'customer_phone' => 'nullable',
            'description' => 'nullable',
            'manager_description' => 'nullable',
        ];
    }
}
