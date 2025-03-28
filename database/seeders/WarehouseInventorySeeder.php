<?php

namespace Database\Seeders;

use App\Models\WarehouseShelf;
use App\Models\WarehouseInventory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WarehouseInventorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $shelves = WarehouseShelf::all();

        $sampleItems = [
            [
                'item_number' => '040007895',
                'item_name' => 'EcoTank L5290',
                'grade' => 'Good',
                'batch_number' => '1041065',
                'bom_unit' => 'ROL',
                'physical_inventory' => 19,
                'physical_reserved' => 19,
                'actual_count' => 19,
            ],
            [
                'item_number' => '040007896',
                'item_name' => 'NVME SSD 1TB',
                'grade' => 'Good',
                'batch_number' => '1041066',
                'bom_unit' => 'ROL',
                'physical_inventory' => 25,
                'physical_reserved' => 20,
                'actual_count' => 25,
            ],
            [
                'item_number' => '040007897',
                'item_name' => 'Notebook A5 80gsm',
                'grade' => 'Fair',
                'batch_number' => '1041067',
                'bom_unit' => 'ROL',
                'physical_inventory' => 15,
                'physical_reserved' => 10,
                'actual_count' => 15,
            ],
            [
                'item_number' => '040007898',
                'item_name' => 'LAPTOP DELL LATITUDE 5410',
                'grade' => 'Good',
                'batch_number' => '1041068',
                'bom_unit' => 'ROL',
                'physical_inventory' => 30,
                'physical_reserved' => 25,
                'actual_count' => 30,
            ],
            [
                'item_number' => '040007899',
                'item_name' => 'LAPTOP DELL LATITUDE 5420',
                'grade' => 'New',
                'batch_number' => '2041069',
                'bom_unit' => 'UNIT',
                'physical_inventory' => 5,
                'physical_reserved' => 3,
                'actual_count' => 5,
            ],
        ];

        foreach ($shelves as $shelf) {
            foreach ($sampleItems as $item) {
                WarehouseInventory::create([
                    ...$item,
                    'location_code' => $shelf->location_code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
