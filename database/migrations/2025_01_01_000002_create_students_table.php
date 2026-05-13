<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * students — mahasiswa profile table.
     * Stores academic data, current lifecycle status, and DPM assignment.
     *
     * access_status lifecycle:
     *   Unverified → PendingReview → ApprovedForm1 / RejectedForm1
     *   → HasApplication → HasDPM → LogbookComplete
     *   → MenungguSidang → SiklusSelesai
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dpm_id')->nullable()->constrained('lecturers')->nullOnDelete();
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('study_program');
            $table->string('email');
            $table->string('semester')->nullable();           // e.g. "6"
            $table->string('tahun_akademik')->nullable();     // e.g. "2024/2025"
            $table->string('jumlah_sks')->nullable();         // e.g. "120"
            $table->string('ipk')->nullable();                // e.g. "3.75"
            $table->enum('access_status', [
                'Unverified',
                'PendingReview',
                'RejectedForm1',
                'ApprovedForm1',
                'HasApplication',
                'HasDPM',
                'LogbookComplete',
                'MenungguSidang',
                'SiklusSelesai',
            ])->default('Unverified');
            $table->boolean('is_independent')->default(false); // Mandiri track marker
            $table->json('form1_data')->nullable();            // stored Form 1 field values
            $table->string('form1_pdf_path')->nullable();      // generated PDF path
            $table->string('form1_rejection_reason')->nullable();
            $table->foreignId('form1_approved_by')->nullable()->constrained('lecturers')->nullOnDelete();
            $table->timestamp('form1_approved_at')->nullable();
            $table->integer('approved_logbook_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
