<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            DepartmentSeeder::class,
            BuildingSeeder::class,
            TicketCategorySeeder::class, // Add this line
            RoleSeeder::class,
        ]);
    }
}
