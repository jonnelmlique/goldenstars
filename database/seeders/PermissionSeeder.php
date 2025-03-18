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
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
