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
        $listings = VehicleListing::with('images', 'features')->latest()->simplePaginate(10);
        return $this->success($listings, 'Vehicle listings retrieved');
    }

    public function store(StoreVehicleListingRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $listing = VehicleListing::create($data);

        if ($request->filled('vehicle_features')) {
            $listing->features()->sync($request->features);
        }

        if ($request->hasFile('images')) {
            $this->uploadImage($listing, $request->file('images'));
        }

        return $this->success($listing->load(['images', 'features']), 'Vehicle listing created', 201);
    }

    public function edit($id)
    {
        return VehicleListing::with('images', 'features')->findOrFail($id);
    }

    public function show(VehicleListing $vehicleListing)
    {
        return $this->success($vehicleListing->load(['images', 'features']), 'Vehicle listing retrieved');
    }

    public function showBySlug($slug)
    {
        $vehicleListing = VehicleListing::with('images', 'displayImage', 'features')
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->success($vehicleListing, 'Vehicle listing retrieved successfully');
    }

    public function search(Request $request)
    {
        $query = VehicleListing::query();

        if ($term = $request->input('q')) {
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%$term%")
                    ->orWhere('vin', 'like', "%$term%")
                    ->orWhere('trim', 'like', "%$term%")
                    ->orWhere('color', 'like', "%$term%")
                    ->orWhereHas('carMake', fn($q) => $q->where('name', 'like', "%$term%"))
                    ->orWhereHas('carModel', fn($q) => $q->where('name', 'like', "%$term%"));
            });
        }

        $results = $query->with(['carMake', 'carModel', 'images', 'features'])
            ->latest()
            ->simplePaginate(10);

        return $this->success($results, 'Vehicle listings search results.');
    }


    public function update(UpdateVehicleListingRequest $request, VehicleListing $vehicleListing)
    {
        $vehicleListing->update($request->validated());

        if ($request->hasFile('images')) {
            $this->deleteAllImages($vehicleListing);
            $this->uploadImage($vehicleListing, $request->file('images'));
        }

        if ($request->filled('features')) {
            $vehicleListing->features()->sync($request->features); // Replace all
        }

        return $this->success($vehicleListing->load(['images', 'features']), 'Vehicle listing updated');
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
