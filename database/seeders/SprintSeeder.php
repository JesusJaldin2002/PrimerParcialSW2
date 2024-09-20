<?php

namespace Database\Seeders;

use App\Models\Sprint;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SprintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sprint::create([
            'project_id' => 1,
            'name' => '1',
            'start_date' => now(),
            'end_date' => now()->addDays(7),
        ]);

        Sprint::create([
            'project_id' => 1,
            'name' => '2',
            'start_date' => now()->addDays(8),
            'end_date' => now()->addDays(15),
        ]);
    }
}
