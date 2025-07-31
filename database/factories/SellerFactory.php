<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seller>
 */
class SellerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => function () {
                $user = User::factory()->create([
                    'email' => 'seller@example.com',
                    'password' => bcrypt('password123')
                ]);
                $user->assignRole('seller');
                return $user->id;
            },
            'store_name' => $store = fake()->unique()->company,
            'slug' => Str::slug($store) . '-' . Str::random(5),
            'description' => fake()->optional()->paragraph,
            'email' => fake()->unique()->safeEmail,
            'phone' => fake()->optional()->phoneNumber,
            'address' => fake()->optional()->address,
            'image_url' => fake()->optional()->imageUrl(),
        ];
    }
}
