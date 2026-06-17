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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            // Lien avec le client (nullable si client de passage sans compte)
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            // Lien avec l'utilisateur/vendeur connecté qui fait la vente
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->decimal('subtotal', 10, 2); // Somme des produits avant remise
            $table->decimal('discount', 10, 2)->default(0.00); // Remise appliquée
            $table->decimal('total', 10, 2); // Net à payer
            
            $table->enum('payment_method', ['Espèces', 'Mobile Money', 'Virement']);
            $table->string('invoice_number')->unique(); // Numéro de facture unique
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};