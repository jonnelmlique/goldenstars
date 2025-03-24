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
                'name' => 'Office Paper A4',
                'sku' => 'PAP-A4-001',
                'description' => 'Standard A4 printing paper',
                'quantity' => 500,
                'unit' => 'reams'
            ],
            [
                'name' => 'Ink Cartridge Black',
                'sku' => 'INK-BLK-001',
                'description' => 'Black ink cartridge for HP printers',
                'quantity' => 50,
                'unit' => 'pieces'
            ],
            [
                'name' => 'Office Chairs',
                'sku' => 'FRN-CHR-001',
                'description' => 'Ergonomic office chairs',
                'quantity' => 20,
                'unit' => 'pieces'
            ],
            [
                'name' => 'Laptops',
                'sku' => 'EQP-LPT-001',
                'description' => 'Company standard laptops',
                'quantity' => 10,
                'unit' => 'units'
            ],
        ];

        foreach ($shelves as $shelf) {
            // Add 2 random items to each shelf
            for ($i = 0; $i < 2; $i++) {
                $item = $sampleItems[array_rand($sampleItems)];
                WarehouseInventory::create([
                    'shelf_id' => $shelf->id,
                    'name' => $item['name'],
                    'sku' => $item['sku'] . "-{$shelf->id}-{$i}",
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'shelf_position' => $i + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
