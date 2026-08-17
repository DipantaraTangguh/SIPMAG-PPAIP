<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel riwayat magang yang "selalu tercatat": satu baris per siklus yang
     * selesai (wajib maupun non-wajib). Append-only snapshot yang tetap ada
     * walau siklus aktif di-reset.
     */
    public function up(): void
    {
        Schema::create('internship_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->unsignedInteger('cycle_number');
            $table->enum('jenis_magang', ['wajib', 'non_wajib']);
            $table->enum('outcome_status', ['CycleCompleted', 'ElectiveCompleted']);

            // Snapshot identitas mahasiswa saat siklus selesai.
            $table->string('nim');
            $table->string('nama');
            $table->string('study_program');
            $table->unsignedTinyInteger('semester')->nullable();
            $table->decimal('ipk', 3, 2)->nullable();

            // Snapshot Form 1.
            $table->string('skema_magang')->nullable();
            $table->text('topik_magang')->nullable();
            $table->string('output_target')->nullable();

            // Snapshot tempat & periode magang (Form 2).
            $table->string('company_name')->nullable();
            $table->text('alamat_perusahaan')->nullable();
            $table->string('nama_pimpinan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            // Snapshot penilaian (hanya untuk magang wajib).
            $table->decimal('final_score', 5, 2)->nullable();
            $table->string('letter_grade')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'cycle_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_cycles');
    }
};
