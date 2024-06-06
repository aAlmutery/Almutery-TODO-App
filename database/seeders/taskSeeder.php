<?php

namespace Database\Seeders;

use App\Models\Tasks;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class taskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tasks::factory(10)->create();

        Tasks::create([
            'title' => fake()->name(),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'created_by' => 1,
            'status' => 1,
            'parent' => 1,
            'assign_to' => 3
        ]);

        Tasks::create([
            'title' => fake()->name(),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'created_by' => 1,
            'status' => 1,
            'parent' => 11,
            'assign_to' => 3
        ]);

        Tasks::create([
            'title' => fake()->name(),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'created_by' => 1,
            'status' => 1,
            'parent' => 12,
            'assign_to' => 3        ]);

        Tasks::create([
            'title' => fake()->name(),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'created_by' => 1,
            'status' => 1,
            'parent' => 1,
            'assign_to' => 2
        ]);

        Tasks::create([
            'title' => fake()->name(),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'created_by' => 1,
            'status' => 1,
            'parent' => 1,
            'assign_to' => 3        ]);

        Tasks::create([
            'title' => fake()->name(),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'created_by' => 1,
            'status' => 1,
            'parent' => 1,
            'assign_to' => 3        ]);
        
        Tasks::create([
            'title' => fake()->name(),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'created_by' => 1,
            'status' => 1,
            'parent' => 1,
            'assign_to' => 3        ]);
    }
}
