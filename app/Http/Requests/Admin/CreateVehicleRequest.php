<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateVehicleRequest extends FormRequest
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
            'name' => 'nullable|max:70',
            'family' => 'nullable|max:70',
            'mobile' => 'Required|regex:/(09)[0-9]{9}/|digits:11|numeric|unique:users,mobile',
            'password' => 'required|min:5',
            'nationalCode' => 'nullable|digits:10|numeric',
            'nationalPhoto' => 'nullable|mimes:jpeg,png|max:15360|dimensions:min_width=100,min_height=100',
            'address' => 'nullable|max:255',
            'postCode' => 'nullable|digits:10|numeric',
            'phone' => 'nullable|numeric',
            'status' => 'nullable|in:1,0,-1,-2',
            'type' => 'required|in:MOTOR,CAR',
            'brand' => 'required|max:150',
            'pelak' => 'required|max:50',
            'color' => 'required|max:50',
            'model' => 'required|max:150',
            'neighborhoodAvailable' => 'required',
            'neighborhoodAvailable.*' => 'integer|exists:neighborhoods,id',
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
            'nationalPhoto' => 'تصویر کارت ملی',
            'postCode' => 'کد پستی',
            'nationalCode' => 'کد ملی',
        ];
    }
}
