<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = Permission::create(['name' => 'Create User']); // 1
        $permission = Permission::create(['name' => 'Update User']); // 2

        $permission = Permission::create(['name' => 'Create Task']); // 3
        $permission = Permission::create(['name' => 'Delete Task']); // 4

        // Use these in Update
        $permission = Permission::create(['name' => 'Can Move To In Prograss']); // 5
        $permission = Permission::create(['name' => 'Can Move To Ready For Test']); // 6
        $permission = Permission::create(['name' => 'Can Move To PO Review']); // 7
        $permission = Permission::create(['name' => 'Can Move To Reject']); // 8

        // Use these in Edit
        $permission = Permission::create(['name' => 'Have Action On TODO']); // 9
        $permission = Permission::create(['name' => 'Have Action On In Prograss']); // 10
        $permission = Permission::create(['name' => 'Have Action On Ready For Test']); // 11
        $permission = Permission::create(['name' => 'Have Action On PO Review']); // 12
        $permission = Permission::create(['name' => 'Have Action On Reject']); // 13

        $permission = Permission::create(['name' => 'Can Move To Done']); // 14

    }
}
