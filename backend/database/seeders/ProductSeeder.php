<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Article Consommable (Stock OK)
        Product::create([
            'name' => 'Papier Rame A4 80g',
            'reference' => 'REF-PAP-A4',
            'category' => 'Consommable',
            'purchase_price' => 2500.00,
            'selling_price' => 3500.00,
            'stock_quantity' => 45,
            'alert_threshold' => 10,
            'supplier' => 'S超 Malik Papeterie',
        ]);

        // 2. Article Consommable (En Stock Critique !)
        Product::create([
            'name' => 'Cartouche Encre HP Noir 652',
            'reference' => 'REF-ENC-HP652',
            'category' => 'Consommable',
            'purchase_price' => 9000.00,
            'selling_price' => 12500.00,
            'stock_quantity' => 2,
            'alert_threshold' => 5,
            'supplier' => 'TechDistri Cameroun',
        ]);

        // 3. Article Matériel (Stock OK)
        Product::create([
            'name' => 'Clé USB 64 Go Kingston',
            'reference' => 'REF-USB-64K',
            'category' => 'Matériel',
            'purchase_price' => 3000.00,
            'selling_price' => 5000.00,
            'stock_quantity' => 15,
            'alert_threshold' => 3,
            'supplier' => 'S超 Malik Papeterie',
        ]);
    }
}