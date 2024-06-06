<?php

namespace Database\Seeders;

use App\Models\StatusList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // StatusList::truncate();

        StatusList::firstOrCreate(['name' => 'TODO']); // 1
        StatusList::firstOrCreate(['name' => 'IN PROGRESS']); // 2
        StatusList::firstOrCreate(['name' => 'READY FOR TEST']); // 3
        StatusList::firstOrCreate(['name' => 'PO REVIEW']); // 4
        StatusList::firstOrCreate(['name' => 'DONE']); // 5
        StatusList::firstOrCreate(['name' => 'REJECTED']); // 6

    }
}
