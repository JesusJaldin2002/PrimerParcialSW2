<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Jesus Jaldin', 'email' => 'jesus@gmail.com', 'password' => bcrypt('12345678')],
            ['name' => 'Alan Romero', 'email' => 'alan@gmail.com', 'password' => bcrypt('12345678')],
            ['name' => 'Melissa Fisher', 'email' => 'melissa@gmail.com', 'password' => bcrypt('12345678')],
            ['name' => 'Veronica Antezana', 'email' => 'veronica@gmail.com', 'password' => bcrypt('12345678')],
            ['name' => 'Milenka Rojas', 'email' => 'milenka@gmail.com', 'password' => bcrypt('12345678')],
        ];

        foreach ($users as $user) {
            User::create($user)->assignRole('Admin');
        }
    }
}
