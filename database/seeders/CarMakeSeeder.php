<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\CarMake;

class CarMakeSeeder extends Seeder
{
    public function run()
    {
        $page = 1;
        $countInserted = 0;

        do {
            $response = Http::timeout(30)
                ->get('https://vpic.nhtsa.dot.gov/api/vehicles/getallmakes', [
                    'format' => 'json',
                    'page' => $page,
                ]);

            $body = $response->json();

            $results = $body['Results'] ?? [];

            if (!$results) {
                break;
            }

            foreach ($results as $item) {
                CarMake::updateOrCreate(
                    ['id' => $item['Make_ID']],
                    ['name' => $item['Make_Name']]
                );
                $countInserted++;
            }

            $page++;
        } while (count($results) === 1000); // continue if full batch

        $this->command->info("Inserted or updated {$countInserted} car makes.");
    }
}
