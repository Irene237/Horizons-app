<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Nom du produit
        $table->string('reference')->unique(); // Référence unique
        $table->enum('category', ['Matériel', 'Consommable']); // Catégorie restreinte
        $table->decimal('purchase_price', 10, 2); // Prix d'achat
        $table->decimal('selling_price', 10, 2); // Prix de vente
        $table->integer('stock_quantity')->default(0); // Quantité en stock
        $table->integer('alert_threshold')->default(5); // Seuil d'alerte de stock
        $table->string('image_path')->nullable(); // Photo du produit (upload path)
        $table->string('supplier')->nullable(); // Fournisseur associé
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
