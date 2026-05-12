<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * applications — Portal Mitra track applications.
     * Each row = one student applying to one internship vacancy.
     *
     * Status lifecycle:
     *   Applied → Accepted (triggers auto-cancel of others)
     *           → RejectedByCompany
     *           → Canceled (auto-cancel by observer)
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('internship_id')->nullable()->constrained('internships')->nullOnDelete();
            $table->string('cv_file_path')->nullable();             // uploaded CV (Portal track)
            $table->string('loa_path')->nullable();                 // LoA uploaded later (both tracks)
            $table->enum('status', [
                'Applied',
                'Accepted',
                'RejectedByCompany',
                'Canceled',
            ])->default('Applied');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
