<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::create([
            'name' => 'HU1',
            'description' => 'Descripción de la Tarea 1',
            'status' => 'to do',
            'priority' => 'high'
        ]);

        Task::create([
            'name' => 'HU2',
            'description' => 'Descripción de la Tarea 2',
            'status' => 'in progress',
            'priority' => 'medium'
        ]);

        Task::create([
            'name' => 'HU3',
            'description' => 'Descripción de la Tarea 3',
            'status' => 'done',
            'priority' => 'low'
        ]);

        Task::create([
            'name' => 'HU27',
            'description' => 'Descripción de la Tarea 27',
            'status' => 'done',
            'priority' => 'low'
        ]);
    }
}
