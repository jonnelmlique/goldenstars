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

        $allItems = [
            [
                'item_number' => '040007895',
                'item_name' => 'LAPTOP DELL LATITUDE 5420',
                'grade' => 'New',
                'batch_number' => '2041069',
                'bom_unit' => 'UNIT',
                'physical_inventory' => 5,
                'physical_reserved' => 3,
                'actual_count' => 5,
            ],
            [
                'item_number' => '040007896',
                'item_name' => 'PRINTER EPSON L5290',
                'grade' => 'Good',
                'batch_number' => '2041070',
                'bom_unit' => 'UNIT',
                'physical_inventory' => 3,
                'physical_reserved' => 1,
                'actual_count' => 3,
            ],
            [
                'item_number' => '040007897',
                'item_name' => 'KEYBOARD MECHANICAL RGB',
                'grade' => 'New',
                'batch_number' => '2041071',
                'bom_unit' => 'PCS',
                'physical_inventory' => 10,
                'physical_reserved' => 5,
                'actual_count' => 10,
            ],
            [
                'item_number' => '040007898',
                'item_name' => 'MONITOR DELL 24" IPS',
                'grade' => 'Good',
                'batch_number' => '2041072',
                'bom_unit' => 'UNIT',
                'physical_inventory' => 8,
                'physical_reserved' => 4,
                'actual_count' => 8,
            ],
            [
                'item_number' => '040007899',
                'item_name' => 'MOUSE LOGITECH G502',
                'grade' => 'New',
                'batch_number' => '2041073',
                'bom_unit' => 'PCS',
                'physical_inventory' => 15,
                'physical_reserved' => 7,
                'actual_count' => 15,
            ],
        ];

        foreach ($shelves as $shelf) {
            // Get random 2 items for each shelf
            $shelfItems = collect($allItems)->random(2)->toArray();

            foreach ($shelfItems as $item) {
                // Modify item number to make it unique per shelf
                $item['item_number'] = $item['item_number'] . '-' . $shelf->id;

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
