<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


/**
 * @OA\Schema(
 *     schema="UpdateStoreTripRequest",
 *     required={"destination_id", "vehicle_type", "shipment_prepare_time", "trip_rial_fare"},
 *     @OA\Property(property="destination_id", type="integer", description="The ID of the destination neighborhood"),
 *     @OA\Property(property="vehicle_type", type="integer", description="The type of vehicle",description="0 => MOTOR, 1 => CAR"),
 *     @OA\Property(property="shipment_prepare_time", type="string", format="date", description="The time for shipment preparation"),
 *     @OA\Property(property="trip_rial_fare", type="number", format="float", description="The fare for the trip"),
 *     @OA\Property(property="customer_name", type="string", maxLength=50, nullable=true, description="The name of the customer"),
 *     @OA\Property(property="customer_phone", type="string", maxLength=50, nullable=true, description="The phone number of the customer"),
 *     @OA\Property(property="description", type="string", maxLength=255, nullable=true, description="The description of the trip")
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
            'destination_id' => 'required|numeric|exists:neighborhoods,id',
            'vehicle_type' => 'required|numeric',
            'shipment_prepare_time' => 'required|date',
            'trip_rial_fare' => 'required|numeric',
            'customer_name' => 'nullable|max:50',
            'customer_phone' => 'nullable|max:50',
            'description' => 'nullable|max:255',
        ];
    }
}
