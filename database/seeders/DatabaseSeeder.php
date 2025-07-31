<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            //CountrySeeder::class,
            //StateSeeder::class,
            //DeliveryAreaSeeder::class,
            RolesTableSeeder::class,
            UserSeeder::class,
            ProductCategorySeeder::class,
            SellerSeeder::class,
            SqlSeeder::class
        ]);
    }
}
