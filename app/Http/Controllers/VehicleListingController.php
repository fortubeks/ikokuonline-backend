<?php

namespace App\Http\Controllers;

use App\Models\VehicleListing;
use App\Models\VehicleListingImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreVehicleListingRequest;
use App\Http\Requests\UpdateVehicleListingRequest;
use App\Traits\ApiResponse;

class VehicleListingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $listings = VehicleListing::with('images')->latest()->simplePaginate(10);
        return $this->success($listings, 'Vehicle listings retrieved');
    }

    public function store(StoreVehicleListingRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $listing = VehicleListing::create($data);

        $this->uploadImage($listing, $request->file('images'));

        return $this->success($listing->load('images'), 'Vehicle listing created', 201);
    }

    public function show(VehicleListing $vehicleListing)
    {
        return $this->success($vehicleListing->load('images'), 'Vehicle listing retrieved');
    }

    public function update(UpdateVehicleListingRequest $request, VehicleListing $vehicleListing)
    {
        $vehicleListing->update($request->validated());

        if ($request->hasFile('images')) {
            $this->deleteAllImages($vehicleListing);
            $this->uploadImage($vehicleListing, $request->file('images'));
        }

        return $this->success($vehicleListing->load('images'), 'Vehicle listing updated');
    }

    public function destroy(VehicleListing $vehicleListing)
    {
        $vehicleListing->delete();
        return $this->success(null, 'Vehicle listing deleted');
    }

    public function uploadImages(Request $request, VehicleListing $vehicleListing)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|max:2048'
        ]);

        $existing = $vehicleListing->images()->count();
        $new = count($request->file('images'));

        if ($existing + $new > 5) {
            return $this->error('Maximum of 5 images allowed');
        }

        $uploaded = $this->uploadImage($vehicleListing, $request->file('images'));

        return $this->success($uploaded, 'Images uploaded', 201);
    }

    public function deleteImage(VehicleListing $vehicleListing, VehicleListingImage $image)
    {
        if ($image->vehicle_listing_id !== $vehicleListing->id) {
            return $this->error('Image does not belong to this listing', [], 403);
        }

        if ($vehicleListing->images()->count() <= 1) {
            return $this->error('At least one image must remain', [], 422);
        }

        Storage::disk('public')->delete($image->getRawOriginal('path'));
        $image->delete();

        return $this->success(null, 'Image deleted');
    }

    private function uploadImage(VehicleListing $listing, array $images)
    {
        $uploaded = [];

        foreach ($images as $image) {
            $path = $image->store('vehicles', 'public');
            $uploaded[] = $listing->images()->create(['path' => $path]);
        }

        return $uploaded;
    }

    private function deleteAllImages(VehicleListing $listing)
    {
        foreach ($listing->images as $image) {
            Storage::disk('public')->delete($image->getRawOriginal('path'));
            $image->delete();
        }
    }
}
