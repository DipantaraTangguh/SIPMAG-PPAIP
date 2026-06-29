<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('defense_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defense_submission_id')
                ->constrained('sidang_submissions')
                ->restrictOnDelete();
            $table->foreignId('lecturer_id')
                ->constrained('lecturers')
                ->restrictOnDelete();
            $table->enum('assessor_role', ['dpm', 'penguji_1', 'penguji_2']);
            $table->decimal('internship_performance_score', 5, 2);
            $table->decimal('final_report_score', 5, 2);
            $table->decimal('presentation_score', 5, 2);
            $table->timestamps();

            $table->unique(
                ['defense_submission_id', 'assessor_role'],
                'defense_assessment_submission_role_unique'
            );
            $table->index(
                ['lecturer_id', 'defense_submission_id'],
                'defense_assessment_lecturer_submission_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('defense_assessments');
    }
};
