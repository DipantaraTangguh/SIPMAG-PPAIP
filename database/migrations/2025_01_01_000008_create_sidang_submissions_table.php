<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * sidang_submissions — final document uploads before the offline defense.
     * One record per student per cycle. No approval/rejection — stored immediately.
     * PPAIP manually resets the cycle after confirming offline sidang.
     */
    public function up(): void
    {
        Schema::create('sidang_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('laporan_path');                 // Laporan Magang Akhir PDF
            $table->string('poster_path');                  // Poster Presentasi PDF
            $table->string('krs_path');                     // KRS proof of internship course PDF
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidang_submissions');
    }
};
