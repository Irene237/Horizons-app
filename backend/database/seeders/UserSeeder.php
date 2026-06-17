<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function up(): void {} // On laisse vide si requis, mais concentrons-nous sur run()

    public function run(): void
    {
        // 1. Profil Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@horizon.com',
            'password' => Hash::make('password123'),
            'role' => 'Super Admin',
        ]);

        // 2. Profil Vendeur
        User::create([
            'name' => 'Alice Vendeuse',
            'email' => 'vendeur@horizon.com',
            'password' => Hash::make('password123'),
            'role' => 'Vendeur',
        ]);

        // 3. Profil Agent Impression
        User::create([
            'name' => 'Bob Impression',
            'email' => 'impression@horizon.com',
            'password' => Hash::make('password123'),
            'role' => 'Agent Impression',
        ]);

        // 4. Profil Formateur
        User::create([
            'name' => 'Jean Formateur',
            'email' => 'formateur@horizon.com',
            'password' => Hash::make('password123'),
            'role' => 'Formateur',
        ]);
    }
}