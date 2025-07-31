<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\VehicleFeature;

use App\Traits\ApiResponse;

class VehicleFeatureController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(VehicleFeature::all(), 'Features retrieved');
    }

    public function show(VehicleFeature $feature)
    {
        return $this->success($feature, 'Feature retrieved');
    }

    public function store(Request $request)
    {
        $name = ucwords(strtolower($request->input('name')));

        $request->merge(['name' => $name]);

        $data = $request->validate([
            'name' => 'required|string|unique:vehicle_features,name',
        ]);

        $feature = VehicleFeature::create($data);

        return $this->success($feature, 'Feature created', 201);
    }

    public function update(Request $request, VehicleFeature $feature)
    {
        $name = ucwords(strtolower($request->input('name')));
        $request->merge(['name' => $name]);

        $data = $request->validate([
            'name' => 'required|string|unique:vehicle_features,name,' . $feature->id,
        ]);

        $feature->update($data);
        return $this->success($feature, 'Feature updated');
    }


    public function destroy(VehicleFeature $feature)
    {
        $feature->delete();
        return $this->success(null, 'Feature deleted');
    }
}
