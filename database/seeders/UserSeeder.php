<?php

namespace Database\Seeders;

use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@quizball.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Editor User',
            'email' => 'editor@quizball.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Editor,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Regular User',
            'email' => 'user@quizball.com',
            'password' => Hash::make('password'),
            'role' => UserRole::User,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::User,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::User,
            'is_active' => true,
        ]);
    }
}