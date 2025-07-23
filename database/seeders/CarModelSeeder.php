<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\CarMake;
use App\Models\CarModel;

class CarModelSeeder extends Seeder
{
    public function run()
    {
        //$makes = CarMake::all();
        $makes = CarMake::doesntHave('models')->get();

        if ($makes->isEmpty()) {
            $this->command->error("No car makes found in the database. Please run the CarMakeSeeder first.");
            return;
        }

        foreach ($makes as $make) {
            $makeName = Str::slug($make->name); // Make the name URL-friendly for the API

            $this->command->info("Fetching models for: {$makeName}");

            try {
                $response = Http::retry(3, 1000)->get("https://vpic.nhtsa.dot.gov/api/vehicles/getmodelsformake/{$makeName}?format=json");
                
                if ($response->failed()) {
                    $this->command->warn("Failed to fetch models for {$makeName}");
                    continue;
                }

                $models = $response->json('Results');

                foreach ($models as $model) {
                    CarModel::updateOrCreate(
                        [
                            'external_id' => $model['Model_ID'],
                            'name' => $model['Model_Name'],
                            'car_make_id' => $make->id
                        ],
                        []
                    );
                }

                $this->command->info("Seeded " . count($models) . " models for {$makeName}");
            } catch (\Exception $e) {
                $this->command->error("Error fetching models for {$makeName}: " . $e->getMessage());
                continue;
            }

            sleep(1); // To avoid hitting rate limits
        }
    }
}
