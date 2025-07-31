<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ProductCategory;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_product_category()
    {
        $response = $this->postJson('/api/product-categories', [
            'name' => 'Electronics',
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Electronics']);

        $this->assertDatabaseHas('product_categories', ['name' => 'Electronics']);
    }

    
    public function test_it_can_fetch_all_product_categories()
    {
        ProductCategory::factory()->create(['name' => 'Phones']);
        ProductCategory::factory()->create(['name' => 'Laptops']);

        $response = $this->getJson('/api/product-categories');

        $response->assertOk()
                 ->assertJsonFragment(['name' => 'Phones'])
                 ->assertJsonFragment(['name' => 'Laptops']);
    }

    public function test_it_can_fetch_a_single_product_category()
    {
        $category = ProductCategory::factory()->create(['name' => 'TVs']);

        $response = $this->getJson("/api/product-categories/{$category->id}");

        $response->assertOk()
                 ->assertJsonFragment(['name' => 'TVs']);
    }

    public function test_it_can_update_a_product_category()
    {
        $category = ProductCategory::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/product-categories/{$category->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
                 ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('product_categories', ['name' => 'New Name']);
    }

    public function test_it_can_delete_a_product_category()
    {
        $category = ProductCategory::factory()->create();

        $response = $this->deleteJson("/api/product-categories/{$category->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
    }
}

