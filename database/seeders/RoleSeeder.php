<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Building;
use App\Models\Department;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Create admin role
        $adminRole = Role::create([
            'code' => 'ADMIN',
            'name' => 'Administrator',
            'description' => 'Full system access',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Create staff role
        $staffRole = Role::create([
            'code' => 'Staff',
            'name' => 'Staff',
            'description' => 'For Ticket staff member',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Attach permissions
        $adminRole->permissions()->attach(Permission::all());


        // Get first building and department
        $building = Building::first();
        $department = Department::first();

        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'building_id' => $building->id,
            'department_id' => $department->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);


    }
}
