<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SprintTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sprint_task')->insert([
            [
                'sprint_id' => 1,
                'task_id' => 1
            ],
            [
                'sprint_id' => 1,
                'task_id' => 2
            ],
            [
                'sprint_id' => 2,
                'task_id' => 3
            ],
            [
                'sprint_id' => 1,
                'task_id' => 4
            ],
        ]);
    }
}
