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
        // Créer un utilisateur admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@bitchest.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'balance_eur' => 10000,
            'is_active' => true
        ]);

        // Créer un utilisateur client
        User::create([
            'name' => 'User Test',
            'email' => 'user@bitchest.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'balance_eur' => 500,
            'is_active' => true
        ]);

        // Créer d'autres utilisateurs de test
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => Hash::make('password123'),
                'role' => 'client',
                'balance_eur' => 500,
                'is_active' => true
            ]);
        }
    }
}
