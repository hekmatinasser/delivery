<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateStoreRequest extends FormRequest
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
            'storeCategory_id' => 'required|numeric|exists:store_category,id',
            'neighborhood_id' => 'required|numeric|exists:neighborhoods,id',
            'storeAreaType' => 'required|in:RENT,OWNERSHIP',
            'storeName' => 'required|max:255',
            'storeAddress' => 'required|max:255',
            'storePostCode' => 'required|digits:10|numeric',
            'storePhone' => 'required|numeric',
            'storeLat' => 'required|numeric',
            'storeLang' => 'required|numeric',
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
            'storeCategory_id' => 'صنف فعالیت',
            'storeAreaType' => 'نوع ملک',
            'storePhone' => 'تلفن ثابت مغازه',
            'storePostCode' => 'کد پستی مغازه',
            'storeLat' => 'طول جغرافیایی',
            'storeLang' => 'عرض جغرافیایی',
            'nationalCode' => 'کد ملی',
            'neighborhood_id' => 'محله',
        ];
    }
}
