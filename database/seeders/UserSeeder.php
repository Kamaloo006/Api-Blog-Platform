<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            (object)[
                "name" => "Hassan",
                "email" => "hassan@gmail.com",
                "role" => 'admin',
                "password" => Hash::make("123123123"),

            ],
            (object)[
                "name" => "Ahmed",
                "email" => "ahmed@gmail.com",
                "role" => 'user',
                "password" => Hash::make("123123123")
            ],
            (object)[
                "name" => "Mazen",
                "email" => "mazen@gmail.com",
                "role" => 'user',
                "password" => Hash::make("123123123")
            ],
            (object)[
                "name" => "Salah",
                "email" => "salah@gmail.com",
                "role" => 'admin',
                "password" => Hash::make("123123123")
            ],
        ];

        foreach ($users as $user) {
            User::create(['name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'password' => $user->password]);
        }
    }
}
