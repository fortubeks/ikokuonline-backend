<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Traits\ApiResponse;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Product::with('images');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('product_category_id', $categoryId);
        }

        $products = $query->latest()->simplePaginate(10)->appends($request->query());

        return $this->success($products, 'Products fetched successfully');
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $product = Product::create($data);

        $this->uploadImagesToProduct($product, $request->file('images'));

        return $this->success($product->load('images'), 'Product created successfully', 201);
    }

    public function show(Product $product)
    {
        return $this->success($product->load('images'), 'Product retrieved successfully');
    }

    

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $product->update($data);

        if ($request->hasFile('images')) {
            $this->deleteAllProductImages($product);
            $this->uploadImagesToProduct($product, $request->file('images'));
        }

        return $this->success($product->load('images'), 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return $this->success(null, 'Product deleted successfully');
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|max:2048',
            'is_display' => 'nullable|boolean',
        ]);

        $existingCount = $product->images()->count();
        $newCount = count($request->file('images'));

        if ($existingCount + $newCount > 5) {
            return $this->error('Too many images', [
                'images' => ['You can only upload a maximum of 5 images.']
            ]);
        }

        $this->uploadImagesToProduct($product, $request->file('images'), $request->boolean('is_display'));

        return $this->success($product->images, 'Images uploaded successfully', 201);
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            return $this->error('Image does not belong to this product.', [], 403);
        }

        if ($product->images()->count() <= 1) {
            return $this->error('Cannot delete the last image of the product.', [], 422);
        }

        $wasDisplay = $image->is_display;

        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($wasDisplay) {
            $nextImage = $product->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_display' => true]);
            }
        }

        return $this->success(null, 'Image deleted successfully');
    }

    private function uploadImagesToProduct(Product $product, array $images, bool $isDisplay = false): void
    {
        $existingCount = $product->images()->count();

        foreach ($images as $index => $image) {
            $path = $image->store('products', 'public');

            $imageRecord = $product->images()->create([
                'path' => $path,
                'is_display' => $isDisplay || ($existingCount + $index === 0),
            ]);

            if ($isDisplay) {
                $product->images()
                        ->where('id', '!=', $imageRecord->id)
                        ->update(['is_display' => false]);
            }
        }
    }

    private function deleteAllProductImages(Product $product): void
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->getRawOriginal('path'));
            $image->delete();
        }
    }

    public function byCategory($slug)
    {
        $category = ProductCategory::where('slug', $slug)->firstOrFail();

        $products = $category->products()->with('images')->latest()->paginate(10);

        return $this->success($products, 'Products in category: ' . $category->name);
    }
}
