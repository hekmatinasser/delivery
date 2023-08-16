<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     title="UpdateStoreRequest",
 *     description="Updtae Store Request Body",
 *     type="object",
 *     required={
 *         "category_id",
 *         "areaType",
 *         "name",
 *         "address",
 *         "postCode",
 *         "phone",
 *         "lat",
 *         "lang"
 *     },
 *     @OA\Property(
 *         property="category_id",
 *         description="Category ID",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="areaType",
 *         description="Area Type 0 => RENT, 1 => OWNERSHIP",
 *         type="string",
 *         enum={"RENT", "OWNERSHIP"},
 *         example="RENT"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="Name",
 *         type="string",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         description="Address",
 *         type="string",
 *         example="123 Main St"
 *     ),
 *     @OA\Property(
 *         property="postCode",
 *         description="Post Code",
 *         type="integer",
 *         example="1234567890"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         description="Phone",
 *         type="integer",
 *         example="1234567890"
 *     ),
 *     @OA\Property(
 *         property="lat",
 *         description="Latitude",
 *         type="number",
 *         format="desimal",
 *         example="40.7128"
 *     ),
 *     @OA\Property(
 *         property="lang",
 *         description="Longitude",
 *         type="number",
 *         format="desimal",
 *         example="-74.0060"
 *     )
 * )
 */
class UpdateStoreRequest extends FormRequest
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
            'category_id' => 'required|numeric|exists:store_category,id',
            'areaType' => 'required|in:0,1',
            'name' => 'required|max:255',
            'address' => 'required|max:255',
            'postCode' => 'required|digits:10|numeric',
            'phone' => 'required|numeric',
            'lat' => 'required|numeric',
            'lang' => 'required|numeric',
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
            'category_id' => 'صنف فعالیت',
            'areaType' => 'نوع ملک',
            'phone' => 'تلفن ثابت',
            'postCode' => 'کد پستی',
            'lat' => 'طول جغرافیایی',
            'lang' => 'عرض جغرافیایی',
        ];
    }
}
