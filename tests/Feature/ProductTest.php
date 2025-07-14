<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_paginated_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_guest_can_view_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
                 ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_authenticated_user_can_create_product_with_display_image()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $payload = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'slug' => 'test-product-' . rand(1000, 9999),
            'price' => 100.00,
            'stock' => 10,
            'brand' => 'TestBrand',
            'condition' => 'new',
            'can_negotiate' => true,
            'display_image' => UploadedFile::fake()->image('product.jpg'),
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/products', $payload);

        $response->assertCreated()
                 ->assertJsonStructure(['id', 'display_image']);

        Storage::disk('public')->assertExists('products/' . basename($response->json('display_image.path')));
    }

    public function test_authenticated_user_can_update_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/products/{$product->id}", ['name' => 'Updated Name']);

        $response->assertOk()
                 ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_authenticated_user_can_delete_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/products/{$product->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_authenticated_user_can_upload_additional_images()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/products/{$product->id}/images", [
                'images' => [
                    UploadedFile::fake()->image('img1.jpg'),
                    UploadedFile::fake()->image('img2.jpg'),
                ],
            ]);

        $response->assertStatus(201);
        Storage::disk('public')->assertExists('products/img1.jpg');
    }

    public function test_authenticated_user_can_delete_image()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $product = Product::factory()->create(['user_id' => $user->id]);

        $image = $product->images()->create([
            'path' => 'products/to-delete.jpg',
            'is_display' => false,
        ]);

        Storage::disk('public')->put('products/to-delete.jpg', 'fake content');

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/products/{$product->id}/images/{$image->id}");

        $response->assertNoContent();
        Storage::disk('public')->assertMissing('products/to-delete.jpg');
    }
}
