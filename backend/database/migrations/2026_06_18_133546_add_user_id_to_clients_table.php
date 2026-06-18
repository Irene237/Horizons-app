<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('clients', function (Blueprint $table) {
        // On ajoute la colonne après 'id'
        // On définit la contrainte de clé étrangère vers la table 'users'
        $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('clients', function (Blueprint $table) {
        // Pour supprimer la colonne si on annule la migration
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });
}
};
