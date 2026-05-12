<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * supervisor_applications — "Pengajuan Dosen Pembimbing Magang" form.
     * Submitted by both Mitra and Mandiri students after securing an LoA.
     * No approve/reject — goes straight to admin panel for Kaprodi to action.
     */
    public function up(): void
    {
        Schema::create('supervisor_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('company_contact');              // contact person name, phone/email
            $table->string('loa_path');                    // Letter of Acceptance PDF (required)
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_applications');
    }
};
