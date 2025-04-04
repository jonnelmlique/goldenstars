<?php

namespace Database\Seeders;

use App\Models\WarehouseLocation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WarehouseLocationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $locations = [
            [
                'name' => 'Main Storage A',
                'code' => 'MSA',
                'description' => 'Main Storage Area Section A',
                'x_position' => -1,
                'y_position' => 0,
                'z_position' => -1,
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
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($locations as $location) {
            WarehouseLocation::create($location);
        }
    }
}
