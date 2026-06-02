<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nidn')->unique();
            $table->string('lecturer_name');
            $table->string('contact')->nullable();          // Kontak dosen bisa email atau nomor HP.
            $table->string('study_program')->nullable();    // Dipakai buat batasin data Kaprodi per prodi.
            $table->string('signature_path')->nullable();   // Tanda tangan ini ditempel ke PDF Form 1.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
