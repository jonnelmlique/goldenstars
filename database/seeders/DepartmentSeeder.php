<?php

namespace Database\Seeders;

use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $departments = [
            [
                'code' => 'IT',
                'name' => 'Information Technology',
                'description' => 'IT Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'HR',
                'name' => 'Human Resources',
                'description' => 'HR Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Purchasing',
                'name' => 'Purchasing',
                'description' => 'Purchasing Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Sales',
                'name' => 'Sales',
                'description' => 'Sales Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PPIC',
                'name' => 'Production Planning Inventory Control',
                'description' => 'Production Planning Inventory Control Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Warehouse',
                'name' => 'Warehouse',
                'description' => 'Warehouse Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Marketing',
                'name' => 'Marketing',
                'description' => 'Marketing Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'R&D',
                'name' => 'Reserch & Development',
                'description' => 'Reserch & Development Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Conference',
                'name' => 'Conference',
                'description' => 'Conference',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'QA',
                'name' => 'Quality Assurance',
                'description' => 'Quality Assurance Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'Production',
                'name' => 'Production',
                'description' => 'Production Department',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
