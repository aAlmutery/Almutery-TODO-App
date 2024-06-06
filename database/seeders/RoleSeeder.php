<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Owner'])->syncPermissions([1,2,3,4,5,6,7,8,9,10,11,12,13,14]);
        Role::create(['name' => 'Developer'])->syncPermissions([5,6,9,10]);
        Role::create(['name' => 'Tester'])->syncPermissions([7,11]);
    }
}
