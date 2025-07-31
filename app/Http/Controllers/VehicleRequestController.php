<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequestFormRequest;
use App\Models\VehicleRequest;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class VehicleRequestController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $requests = VehicleRequest::with(['make', 'model'])->latest()->get();
        return $this->success($requests);
    }

    public function store(VehicleRequestFormRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $vehicleRequest = VehicleRequest::create($data);
        return $this->success($vehicleRequest, 'Request created', 201);
    }

    public function show(VehicleRequest $vehicleRequest)
    {
        return $this->success($vehicleRequest->load(['make', 'model']));
    }

    public function update(VehicleRequestFormRequest $request, VehicleRequest $vehicleRequest)
    {
        $vehicleRequest->update($request->validated());
        return $this->success($vehicleRequest, 'Request updated');
    }

    public function destroy(VehicleRequest $vehicleRequest)
    {
        $vehicleRequest->delete();
        return $this->success(null, 'Request deleted');
    }
}
