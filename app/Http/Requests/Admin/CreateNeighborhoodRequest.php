<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 *
 * @OA\Schema(
 *     schema="CreateNeighborhoodRequest",
 *     type="object",
 *     required={"name", "code", "status"},
 *     @OA\Property(property="name", type="string", minLength=5, maxLength=25, example="Example Name"),
 *     @OA\Property(property="code", type="string", minLength=5, maxLength=25, example="Example Code"),
 *     @OA\Property(property="status", type="integer", enum={0, 1}, example=1, description="0 => inactive, 1=> active")
 * )
 */
class CreateNeighborhoodRequest extends FormRequest
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
            'name' => 'required|min:5|max:25|unique:neighborhoods,name',
            'code' => 'required|min:5|max:25|unique:neighborhoods,code',
            'status' => 'required|in:0,1',
        ];
    }
}
