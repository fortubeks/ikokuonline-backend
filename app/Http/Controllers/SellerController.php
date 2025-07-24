<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\RegisterSellerRequest;
use App\Http\Requests\UpdateSellerRequest;
use App\Models\Seller;
use App\Traits\ApiResponse;

class SellerController extends Controller
{
    use ApiResponse;

    public function register(RegisterSellerRequest $request)
    {
        $user = auth()->user();

        if ($user->seller) {
            return $this->error('User is already registered as a seller', 409);
        }

        $seller = Seller::create([
            'user_id'    => $user->id,
            'store_name' => $request->store_name,
            'description'=> $request->description,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'address'    => $request->address,
            'image_url'  => $request->image_url,
        ]);

        return $this->success($seller, 'Seller account created successfully', 201);
    }

    public function update(UpdateSellerRequest $request, $id)
    {
        $seller = Seller::findOrFail($id);

        if ($seller->user_id !== Auth::id()) {
            return $this->error('Unauthorized', 403);
        }

        if ($request->has('store_name')) {
            $seller->store_name = $request->store_name;
            $newSlug = Str::slug($request->store_name);
            if ($newSlug !== $seller->slug) {
                $count = Seller::where('slug', 'like', "$newSlug%")
                    ->where('id', '!=', $seller->id)->count();
                $seller->slug = $count ? $newSlug . '-' . ($count + 1) : $newSlug;
            }
        }

        if ($request->hasFile('image')) {
            if ($seller->image_url) {
                $oldPath = str_replace('/storage/', '', $seller->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('sellers', 'public');
            $seller->image_url = Storage::url($path);
        }

        $seller->fill($request->only(['description', 'email', 'phone', 'address']));
        $seller->save();

        return $this->success('Seller updated successfully', $seller);
    }

    public function profile()
    {
        $seller = auth()->user()->seller;

        if (!$seller) {
            return $this->error('Seller profile not found', 404);
        }

        return $this->success($seller, 'Seller profile retrieved');
    }

    public function destroy($id)
    {
        $seller = Seller::findOrFail($id);

        // Ensure the authenticated user owns the seller account
        if ($seller->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Optionally delete image
        if ($seller->image_url) {
            $oldPath = str_replace('/storage/', '', $seller->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $seller->delete();

        return response()->json(['message' => 'Seller deleted successfully']);
    }
}
