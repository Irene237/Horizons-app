<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_hours');
            $table->enum('level', ['Débutant', 'Intermédiaire', 'Avancé']);
            $table->string('trainer_name'); // Formateur assigné
            $table->decimal('price', 10, 2);
            $table->integer('max_capacity'); // Nombre de places max
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};