<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_rates', function (Blueprint $table) {
            $table->id();
            $table->string('support_type'); // flyer, banderole, t-shirt, kakemono, carte de visite, etc.
            $table->decimal('rate', 10, 2); // Le prix en FCFA
            $table->enum('calculation_type', ['m2', 'unit'])->default('unit'); // m² (largeur*hauteur) ou à l'unité
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_rates');
    }
};