<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        User::create([
            'name' => 'Manager',
            'email' => 'manager@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ])->assignRole('Owner');

        User::create([
            'name' => 'Product Owner',
            'email' => 'product@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ])->assignRole('Owner');

        User::create([
            'name' => 'Developer Ali',
            'email' => 'Developer@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ])->assignRole('Developer');

        User::create([
            'name' => 'Almutery',
            'email' => 'Almutery@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ])->assignRole('Developer');
        
        User::create([
            'name' => 'Tester',
            'email' => 'Tester@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ])->assignRole('Tester');

        User::create([
            'name' => 'Mohammed',
            'email' => 'Mohammed@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ])->assignRole('Tester');

    }
}
