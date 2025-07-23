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
            'car_make_id' => 'required|integer|exists:car_make,id',
            'car_model_id' => 'required|exists:car_model,id',
            'year' => 'required|integer|min:1900|max:' . now()->year,
            'trim' => 'required|string',
            'color' => 'required|string',
            'interior_color' => 'required|string',
            'transmission' => 'required|string',
            'vin' => 'required|string',
            'condition' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'contact_info' => 'required|string',
            'vehicle_features' => 'array',
            'vehicle_features.*' => 'exists:vehicle_features,id',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|max:2048',
        ];
    }
}
