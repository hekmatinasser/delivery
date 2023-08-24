<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateVehicleAccessRequest extends FormRequest
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
            'neighborhoodAvailable' => 'nullable',
            'neighborhoodAvailable.*' => 'integer|exists:neighborhoods,id',
            // 'storeAvailable.*.id' => 'nullable|integer|exists:store,id',
            // 'storeAvailable.*.expire' => 'nullable|date',
            // 'storeBlocked.*.id' => 'nullable|integer|exists:store,id',
            // 'storeBlocked.*.expire' => 'nullable|date',
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
            'neighborhoodAvailable' => 'محله های در دسترس',
            'storeAvailable' => 'مغازه های در دسترس',
            'storeBlocked' => 'مغازه های بلاک شده',
        ];
    }
}