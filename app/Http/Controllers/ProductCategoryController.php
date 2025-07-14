<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return ProductCategory::with('children')->get();
    }

    public function show($id)
    {
        return ProductCategory::with('children')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
        ]);

        $category = ProductCategory::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id|not_in:' . $id,
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();

        return response()->json(null, 204);
    }
}
