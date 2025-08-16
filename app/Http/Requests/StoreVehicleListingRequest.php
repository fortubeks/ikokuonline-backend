<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleListingRequest extends FormRequest
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
            'car_make_id' => 'nullable|integer|exists:car_make,id',
            'car_model_id' => 'nullable|exists:car_model,id',
            'year' => 'integer|min:1900|max:' . now()->year,
            'trim' => 'string',
            'color' => 'string',
            'interior_color' => 'string',
            'transmission' => 'string',
            'vin' => 'string',
            'condition' => 'string',
            'price' => 'numeric|min:0',
            'description' => 'string',
            'contact_info' => 'string',
            'vehicle_features' => 'array',
            'vehicle_features.*' => 'exists:vehicle_features,id',
            'images' => 'array|min:1|max:5',
            'images.*' => 'image|max:2048',
        ];
    }
}
