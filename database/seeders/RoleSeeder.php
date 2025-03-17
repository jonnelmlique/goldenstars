<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role
        $adminRole = Role::create([
            'code' => 'ADMIN',
            'name' => 'Administrator',
            'description' => 'Full system access',
        ]);

        // Attach all permissions to admin role
        $adminRole->permissions()->attach(Permission::all());

        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);
    }
}
