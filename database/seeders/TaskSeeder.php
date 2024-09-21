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
            'status' => 'to do',
            'priority' => 'medium'
        ]);

        Task::create([
            'name' => 'HU3',
            'description' => 'Descripción de la Tarea 3',
            'status' => 'in progress',
            'priority' => 'low'
        ]);

        Task::create([
            'name' => 'HU4',
            'description' => 'Descripción de la Tarea 4',
            'status' => 'in progress',
            'priority' => 'high'
        ]);
        Task::create([
            'name' => 'HU5',
            'description' => 'Descripción de la Tarea 5',
            'status' => 'in progress',
            'priority' => 'low'
        ]);
        Task::create([
            'name' => 'HU6',
            'description' => 'Descripción de la Tarea 6',
            'status' => 'done',
            'priority' => 'high'
        ]);
        Task::create([
            'name' => 'HU7',
            'description' => 'Descripción de la Tarea 7',
            'status' => 'done',
            'priority' => 'medium'
        ]);
    }
}
