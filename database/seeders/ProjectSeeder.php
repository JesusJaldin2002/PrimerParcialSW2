<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Project = Project::create([
            'name' => 'Primer Parcial SW2',
            'owner_id' => 1,
            'share_code' => '12345678'
        ]);

        $Project->users()->attach(1);

        $Project2 = Project::create([
            'name' => 'Segundo Parcial SW2',
            'owner_id' => 2,
            'share_code' => '87654321'
        ]);

        $Project2->users()->attach(2);
    }
}
