<?php

namespace Database\Seeders;

use App\Models\DeliveryArea;
use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            'Lagos' => [
                'Ikeja',
                'Lekki',
                'Victoria Island',
                'Yaba',
                'Surulere',
                'Ajah',
                'Ikorodu'
            ],
            'Rivers' => [
                'Port Harcourt',
                'Obio-Akpor',
                'Eleme',
                'Oyigbo',
                'Rumuokoro'
            ],
            'Abuja Federal Capital Territory' => [ // Abuja is in FCT
                'Garki',
                'Wuse',
                'Maitama',
                'Asokoro',
                'Gwarinpa',
                'Lugbe'
            ],
        ];

        foreach ($areas as $stateName => $areaList) {
            $state = State::where('name', $stateName)->where('country_id', 161)->first();

            if ($state) {
                foreach ($areaList as $area) {
                    DeliveryArea::updateOrCreate(
                        ['state_id' => $state->id, 'name' => $area]
                    );
                }
            }
        }
    }
}
