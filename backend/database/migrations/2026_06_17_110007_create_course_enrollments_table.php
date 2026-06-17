<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // L'apprenant
            $table->enum('payment_status', ['Payé', 'Partiel', 'Non payé'])->default('Non payé');
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->string('receipt_number')->unique(); // Pour le reçu PDF exigé
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};