<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $categories = [
            // Hardware Issues
            [
                'name' => 'Computer Hardware',
                'description' => 'Desktop, laptop, monitor, peripherals issues',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Printer Issues',
                'description' => 'Printer, scanner, copier problems',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Network Hardware',
                'description' => 'Network devices, cables, access points',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Software Issues
            [
                'name' => 'Software Installation',
                'description' => 'Software installation or upgrade requests',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'System Access',
                'description' => 'Account access, permissions, passwords',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Email Issues',
                'description' => 'Email related problems and requests',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Network Issues
            [
                'name' => 'Internet Connection',
                'description' => 'Internet connectivity issues',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Network Access',
                'description' => 'Network share, drive mapping issues',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Maintenance
            [
                'name' => 'Preventive Maintenance',
                'description' => 'Regular system maintenance and updates',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Data Backup',
                'description' => 'Data backup and recovery requests',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // Others
            [
                'name' => 'Others',
                'description' => 'Other IT related requests',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($categories as $category) {
            TicketCategory::create($category);
        }
    }
}
