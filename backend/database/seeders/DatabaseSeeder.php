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
        // Module A : Création des comptes (Admin, Gérant, Vendeur)
        $this->call(UserSeeder::class);
        
        // Module B1 : Catalogue des produits initiaux
        $this->call(ProductSeeder::class);

        // NOUVEAUTÉ - Module C2 : Tarifs d'impression de base de l'atelier
        \App\Models\PrintRate::create([
            'support_type' => 'banderole', 
            'rate' => 5000, 
            'calculation_type' => 'm2'
        ]); // 5000 FCFA le m² (Ex: Largeur x Hauteur)

        \App\Models\PrintRate::create([
            'support_type' => 't-shirt', 
            'rate' => 3500, 
            'calculation_type' => 'unit'
        ]); // 3500 FCFA à l'unité

        \App\Models\PrintRate::create([
            'support_type' => 'carte de visite', 
            'rate' => 100, 
            'calculation_type' => 'unit'
        ]); // 100 FCFA la carte

        \App\Models\PrintRate::create([
            'support_type' => 'flyer', 
            'rate' => 150, 
            'calculation_type' => 'unit'
        ]); // 150 FCFA le flyer
        
        \App\Models\PrintRate::create([
            'support_type' => 'kakemono', 
            'rate' => 25000, 
            'calculation_type' => 'unit'
        ]); // 25 000 FCFA le kit complet
    }
}