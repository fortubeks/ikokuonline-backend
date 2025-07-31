<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'user_id' => 1, // assuming user exists or you can create with UserFactory
            'product_category_id' => null,
            'name' => $name,
            'description' => $this->faker->paragraph,
            //'slug' => Str::slug($this->faker->unique()->words(3, true)) . '-' . rand(1000, 9999),
            'slug' => Str::slug($name),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(1, 100),
            'brand' => $this->faker->company,
            'car_make_id' => null,
            'car_model_id' => null,
            'condition' => $this->faker->randomElement(['new', 'used']),
            'can_negotiate' => $this->faker->boolean,
        ];
    }
}
