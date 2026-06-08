<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index(['study_program', 'access_status'], 'students_study_program_access_status_index');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->index(['student_id', 'status'], 'applications_student_status_index');
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->index(['student_id', 'status'], 'logbooks_student_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropIndex('logbooks_student_status_index');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_student_status_index');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_study_program_access_status_index');
        });
    }
};
