<?php

namespace Database\Seeders;

use App\Models\WarehouseLocation;
use App\Models\Building; // Import the Building model
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WarehouseLocationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Fetch building IDs
        $goldenstarsBuilding = Building::where('code', 'Goldenstars')->first()->id;
        $nogatuBuilding = Building::where('code', 'Nogatu')->first()->id;
        $diamondBuilding = Building::where('code', 'Diamond')->first()->id;

        $locations = [
            [
                'name' => 'Main Storage A',
                'code' => 'MSA',
                'description' => 'Main Storage Area Section A',
                'x_position' => -1,
                'y_position' => 0,
                'z_position' => -1,
                'building_id' => $goldenstarsBuilding, // Assign building ID
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Main Storage B',
                'code' => 'MSB',
                'description' => 'Main Storage Area Section B',
                'x_position' => 7,
                'y_position' => 0,
                'z_position' => -1,
                'building_id' => $nogatuBuilding, // Assign building ID
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Quick Access Storage',
                'code' => 'QAS',
                'description' => 'Quick Access Storage Area',
                'x_position' => -1,
                'y_position' => 0,
                'z_position' => 7,
                'building_id' => $diamondBuilding, // Assign building ID
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($locations as $location) {
            WarehouseLocation::create($location);
        }
    }
}
