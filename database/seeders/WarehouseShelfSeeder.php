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
                // Generate location code like "LOCATION A0171"
                $locationCode = sprintf(
                    "LOCATION %s%02d%d%d",
                    strtoupper(substr($location->name, 0, 1)), // First letter of location name
                    $location->id, // Location ID
                    $i, // Shelf level
                    1  // Default position
                );

                WarehouseShelf::create([
                    'location_id' => $location->id,
                    'name' => "Shelf {$i}",
                    'code' => "{$location->code}-S{$i}",
                    'level' => $i,
                    'capacity' => 100,
                    'location_code' => $locationCode,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
