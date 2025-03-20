<?php

namespace Database\Seeders;

use App\Models\Building;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $buildings = [
            [
                'code' => 'Goldenstars',
                'name' => 'Goldenstars',
                'description' => 'Goldenstars',
                'location' => 'Quezon City',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Nogatu',
                'name' => 'Nogatu',
                'description' => 'Nogatu',
                'location' => 'Quezon City',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Diamond',
                'name' => 'Diamond',
                'description' => 'Diamond',
                'location' => 'Quezon City',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Aurora',
                'name' => 'Aurora',
                'description' => 'Aurora',
                'location' => 'Quezon City',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($buildings as $building) {
            Building::create($building);
        }
    }
}
