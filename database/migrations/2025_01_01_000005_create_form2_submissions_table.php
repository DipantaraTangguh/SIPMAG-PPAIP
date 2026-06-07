<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form2_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
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
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form2_submissions');
    }
};
