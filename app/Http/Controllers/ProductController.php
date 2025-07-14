<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index($request)
    {
        $query = Product::with('displayImage');

        // Search by name or description
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->query('category_id')) {
            $query->where('product_category_id', $categoryId);
        }

        return $query->latest()->simplePaginate(10)->appends($request->query()); 
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
            'display_image' => 'required|image|max:2048', // image upload
        ]);

        $data['user_id'] = $request->user()->id; // or auth()->id();

        // Create the product
        $product = Product::create($data);

        // Handle display image upload
        if ($request->hasFile('display_image')) {
            $path = $request->file('display_image')->store('products', 'local');
            //$path = $request->file('display_image')->store('products', 's3');

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'is_display' => true,
            ]);
        }

        return response()->json($product->load('displayImage'), 201);
    }


    public function show(Product $product)
    {
        return $product->load('images');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'sometimes|required|string',
            'slug' => 'sometimes|required|unique:products,slug,' . $product->id,
            'price' => 'sometimes|required|numeric',
            'stock' => 'sometimes|required|integer',
            'brand' => 'nullable|string',
            'condition' => 'sometimes|required|string',
            'can_negotiate' => 'sometimes|required|boolean',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'car_make_id' => 'nullable|integer',
            'car_model_id' => 'nullable|integer',
        ]);

        $product->update($data);

        return $product->load('images');
    }

    public function destroy(Product $product)
    {
        // Also delete images from disk
        foreach ($product->images as $image) {
            Storage::delete($image->path);
        }

        $product->delete();

        return response()->noContent();
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|max:2048', // multiple files
            'is_display' => 'nullable|boolean',
        ]);

        $uploaded = [];

        foreach ($request->file('images', []) as $image) {
            $path = $image->store('products', 'local'); // stores in storage/app/public/products
            //$path = $image->store('products', 's3');

            $isDisplay = $request->input('is_display', false);

            // If it's marked as display, unset previous ones
            if ($isDisplay) {
                $product->images()->update(['is_display' => false]);
            }

            $uploaded[] = ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'is_display' => $isDisplay,
            ]);
        }

        return response()->json($uploaded, 201);
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Image does not belong to this product.'], 403);
        }

        Storage::delete($image->path);
        $image->delete();

        return response()->noContent();
    }
}
