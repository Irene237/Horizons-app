<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // On ajoute le champ role, par défaut un utilisateur sera 'Vendeur'
            $table->string('role')->default('Vendeur');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // En cas de retour en arrière, on supprime la colonne
            $table->dropColumn('role');
        });
    }
};