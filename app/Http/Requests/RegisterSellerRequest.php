<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterSellerRequest extends FormRequest
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
            'store_name'  => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'email'       => 'required|email',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

        ];
    }
}
