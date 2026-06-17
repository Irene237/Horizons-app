<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_enrollment_id')->constrained('course_enrollments')->onDelete('cascade');
            $table->date('attendance_date');
            $table->boolean('is_present')->default(true); // true = Présent, false = Absent
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_attendances');
    }
};