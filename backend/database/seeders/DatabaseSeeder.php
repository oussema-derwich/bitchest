<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Création (ou mise à jour) d'un compte administrateur
        User::updateOrCreate(
            ['email' => 'admin@bitchest.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'balance_eur' => 1000,
            ]
        );

        // Création (ou mise à jour) d'un compte utilisateur normal
        User::updateOrCreate(
            ['email' => 'user@bitchest.com'],
            [
                'name' => 'User',
                'password' => bcrypt('user123'),
                'role' => 'client',
                'is_active' => true,
                'balance_eur' => 500,
            ]
        );

        // Call CryptoSeeder
        $this->call(CryptoSeeder::class);
    }
}
