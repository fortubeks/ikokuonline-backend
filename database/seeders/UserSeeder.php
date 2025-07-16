<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //User::factory(3)->create(['password' => bcrypt('password123')]);

        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123')
        ]);

        $user->assignRole('admin');
    }
}
