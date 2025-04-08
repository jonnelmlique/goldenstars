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
                'item_number' => 'NGT001',
                'item_name' => 'NOGATU MANGOSTEEN COFFEE',
                'batch_number' => 'B240001',
                'bom_unit' => 'BOX',
                'physical_inventory' => 100,
                'physical_reserved' => 20,
                'actual_count' => 80, // Updated: 100 - 20 = 80
            ],
            [
                'item_number' => 'NGT002',
                'item_name' => 'NOGATU BARLEY JUICE',
                'batch_number' => 'B240002',
                'bom_unit' => 'BOX',
                'physical_inventory' => 150,
                'physical_reserved' => 30,
                'actual_count' => 120, // Updated: 150 - 30 = 120
            ],
            [
                'item_number' => 'NGT003',
                'item_name' => 'NOGATU COFFEE MIX',
                'batch_number' => 'B240003',
                'bom_unit' => 'BOX',
                'physical_inventory' => 200,
                'physical_reserved' => 40,
                'actual_count' => 160, // Updated: 200 - 40 = 160
            ],
            [
                'item_number' => 'NGT004',
                'item_name' => 'NOGATU PURE JUICE',
                'batch_number' => 'B240004',
                'bom_unit' => 'BOX',
                'physical_inventory' => 120,
                'physical_reserved' => 25,
                'actual_count' => 95, // Updated: 120 - 25 = 95
            ],
            [
                'item_number' => 'NGT005',
                'item_name' => 'NOGATU CHOCOLATE MIX',
                'batch_number' => 'B240005',
                'bom_unit' => 'BOX',
                'physical_inventory' => 80,
                'physical_reserved' => 15,
                'actual_count' => 65, // Updated: 80 - 15 = 65
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
