<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Le gérant qui a pris la commande
            $table->string('support_type'); // flyer, banderole, etc.
            $table->decimal('width_cm', 8, 2)->nullable();  // Largeur (optionnel si t-shirt/carte)
            $table->decimal('height_cm', 8, 2)->nullable(); // Hauteur (optionnel si t-shirt/carte)
            $table->integer('quantity');
            $table->string('file_path')->nullable(); // Lien vers le fichier PDF/PNG/JPG à imprimer
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            
            // Suivi des statuts (Kanban)
            $table->enum('status', ['En attente', 'En production', 'Prêt', 'Livré'])->default('En attente');
            
            // Différencier un Devis d'une Commande ferme
            $table->boolean('is_quotation')->default(false); // true = Devis, false = Commande
            
            $table->string('document_number')->unique(); // Numéro unique (ex: DEV-0001 ou CMD-0001)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_orders');
    }
};