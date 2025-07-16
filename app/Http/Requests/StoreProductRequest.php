<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'brand' => 'nullable|string',
            'condition' => 'required|string',
            'can_negotiate' => 'required|boolean',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'car_make_id' => 'nullable|integer',
            'car_model_id' => 'nullable|integer',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|max:2048', // each image must be valid
        ];
    }
}
