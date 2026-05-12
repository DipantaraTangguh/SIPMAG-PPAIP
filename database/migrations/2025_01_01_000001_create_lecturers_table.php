<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * lecturers — shared table for Kaprodi and DPM roles.
     * A lecturer can be a Kaprodi (has study_program, scoped access)
     * or a DPM (assigned to supervise students).
     */
    public function up(): void
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nidn')->unique();
            $table->string('lecturer_name');
            $table->string('contact')->nullable();          // email or phone
            $table->string('study_program')->nullable();    // set for Kaprodi scoping
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
