<?php

namespace Database\Seeders;

use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WarehouseShelfSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $locations = WarehouseLocation::all();

        foreach ($locations as $location) {
            // Create 4 shelves for each location
            for ($i = 1; $i <= 4; $i++) {
                WarehouseShelf::create([
                    'location_id' => $location->id,
                    'name' => "Shelf {$i}",
                    'code' => "{$location->code}-S{$i}",
                    'level' => $i,
                    'capacity' => 100,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
