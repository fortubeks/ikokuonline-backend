<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'car_make_id' => 'nullable|integer',
            'car_model_id' => 'nullable|integer',
            'year' => 'nullable|integer|min:1900|max:' . now()->year,
            'trim' => 'nullable|string',
            'color' => 'nullable|string',
            'interior_color' => 'nullable|string',
            'transmission' => 'nullable|string',
            'vin' => 'nullable|string',
            'condition' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'description' => 'sometimes|required|string',
            'contact_info' => 'nullable|string',
            'images' => 'sometimes|required|array|min:1|max:5',
            'images.*' => 'image|max:2048',
        ];
    }
}
