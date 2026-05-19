<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * form2_submissions — Mandiri track "Surat Pengantar Magang".
     * Students may submit multiple Form 2s (one per target company).
     * PPAIP reviews each one independently.
     *
     * Status lifecycle:
     *   PendingReview → ApprovedForm2 (PDF auto-generated)
     *                 → RejectedForm2 (student revises & resubmits)
     */
    public function up(): void
    {
        Schema::create('form2_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('company_name');
            $table->text('alamat_perusahaan');          // company address + postal code
            $table->text('lingkup_magang');             // scope/field of internship
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', [
                'PendingReview',
                'ApprovedForm2',
                'RejectedForm2',
            ])->default('PendingReview');
            $table->text('rejection_reason')->nullable();
            $table->string('pdf_path')->nullable();     // auto-generated after ApprovedForm2
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form2_submissions');
    }
};
