<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequestFormRequest extends FormRequest
{
    public function authorize()
    {
        return true; // update this if using policies
    }

    public function rules()
    {
        return [
            'car_make_id' => 'required|exists:car_makes,id',
            'car_model_id' => 'required|exists:car_models,id',
            'year' => 'nullable|integer|digits:4',
            'trim' => 'nullable|string|max:255',
            'budget_min' => 'required|numeric|min:0',
            'budget_max' => 'required|numeric|gte:budget_min',
        ];
    }
}
