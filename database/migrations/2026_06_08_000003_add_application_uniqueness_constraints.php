<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisor_applications', function (Blueprint $table) {
            $table->unique('student_id', 'supervisor_applications_student_unique');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->unique(['student_id', 'internship_id'], 'applications_student_internship_unique');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropUnique('applications_student_internship_unique');
        });

        Schema::table('supervisor_applications', function (Blueprint $table) {
            $table->dropUnique('supervisor_applications_student_unique');
        });
    }
};
