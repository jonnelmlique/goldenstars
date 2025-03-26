<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'users.view', 'group' => 'Users'],
            ['name' => 'users.create', 'group' => 'Users'],
            ['name' => 'users.edit', 'group' => 'Users'],
            ['name' => 'users.delete', 'group' => 'Users'],

            ['name' => 'departments.view', 'group' => 'Departments'],
            ['name' => 'departments.create', 'group' => 'Departments'],
            ['name' => 'departments.edit', 'group' => 'Departments'],
            ['name' => 'departments.delete', 'group' => 'Departments'],

            ['name' => 'buildings.view', 'group' => 'Buildings'],
            ['name' => 'buildings.create', 'group' => 'Buildings'],
            ['name' => 'buildings.edit', 'group' => 'Buildings'],
            ['name' => 'buildings.delete', 'group' => 'Buildings'],

            ['name' => 'roles.view', 'group' => 'Roles'],
            ['name' => 'roles.create', 'group' => 'Roles'],
            ['name' => 'roles.edit', 'group' => 'Roles'],
            ['name' => 'roles.delete', 'group' => 'Roles'],

            ['name' => 'tickets.view', 'group' => 'Tickets'],
            ['name' => 'tickets.create', 'group' => 'Tickets'],
            ['name' => 'tickets.edit', 'group' => 'Tickets'],
            ['name' => 'tickets.delete', 'group' => 'Tickets'],
            ['name' => 'tickets.assign', 'group' => 'Tickets'],
            ['name' => 'tickets.view.all', 'group' => 'Tickets'],
            ['name' => 'tickets.reports', 'group' => 'Tickets'],

            ['name' => 'ticket_categories.view', 'group' => 'Ticket Categories'],
            ['name' => 'ticket_categories.create', 'group' => 'Ticket Categories'],
            ['name' => 'ticket_categories.edit', 'group' => 'Ticket Categories'],
            ['name' => 'ticket_categories.delete', 'group' => 'Ticket Categories'],

            ['name' => 'inventory.view', 'group' => 'Inventory'],
            ['name' => 'inventory.create', 'group' => 'Inventory'],
            ['name' => 'inventory.edit', 'group' => 'Inventory'],
            ['name' => 'inventory.delete', 'group' => 'Inventory'],

            // Warehouse View
            ['name' => 'warehouse.view', 'group' => 'Warehouse'],

            // Warehouse Inventory
            ['name' => 'warehouse.inventory.view', 'group' => 'Warehouse'],
            ['name' => 'warehouse.inventory.create', 'group' => 'Warehouse'],
            ['name' => 'warehouse.inventory.edit', 'group' => 'Warehouse'],
            ['name' => 'warehouse.inventory.delete', 'group' => 'Warehouse'],

            // Warehouse Locations
            ['name' => 'warehouse.locations.view', 'group' => 'Warehouse'],
            ['name' => 'warehouse.locations.create', 'group' => 'Warehouse'],
            ['name' => 'warehouse.locations.edit', 'group' => 'Warehouse'],
            ['name' => 'warehouse.locations.delete', 'group' => 'Warehouse'],

            // Warehouse Shelves
            ['name' => 'warehouse.shelves.view', 'group' => 'Warehouse'],
            ['name' => 'warehouse.shelves.create', 'group' => 'Warehouse'],
            ['name' => 'warehouse.shelves.edit', 'group' => 'Warehouse'],
            ['name' => 'warehouse.shelves.delete', 'group' => 'Warehouse'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
