<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //check if user is a seller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|numeric',
            'stock' => 'sometimes|required|integer',
            'brand' => 'nullable|string',
            'condition' => 'sometimes|required|string',
            'can_negotiate' => 'sometimes|required|boolean',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'car_make_id' => 'required|integer|exists:car_make,id',
            'car_model_id' => 'required|exists:car_model,id',
            'images' => 'sometimes|required|array|min:1|max:5',
            'images.*' => 'image|max:2048',
        ];
    }
}
