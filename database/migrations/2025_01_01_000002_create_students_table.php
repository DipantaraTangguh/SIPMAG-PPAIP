<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('dpm_id')->nullable()->constrained('lecturers')->nullOnDelete();
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('study_program');
            $table->string('email');
            $table->unsignedTinyInteger('semester')->nullable(); // e.g. 6
            $table->string('tahun_akademik')->nullable();     // e.g. "2024/2025"
            $table->unsignedSmallInteger('jumlah_sks')->nullable(); // e.g. 120
            $table->decimal('ipk', 3, 2)->nullable();         // e.g. 3.75
            $table->enum('access_status', [
                'Unverified',
                'PendingReview',
                'RejectedForm1',
                'ApprovedForm1',
                'HasApplication',
                'HasDPM',
                'LogbookComplete',
                'AwaitingDefense',
                'CycleCompleted',
                'ElectiveCompleted',
                'AwaitingConfirmation',
            ])->default('Unverified');
            $table->boolean('is_independent')->default(false); // Mandiri track marker
            $table->json('form1_data')->nullable();            // stored Form 1 field values
            $table->string('form1_pdf_path')->nullable();      // generated PDF path
            $table->string('form1_rejection_reason')->nullable();
            $table->foreignId('form1_approved_by')->nullable()->constrained('lecturers')->nullOnDelete();
            $table->timestamp('form1_approved_at')->nullable();
            $table->integer('approved_logbook_count')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
