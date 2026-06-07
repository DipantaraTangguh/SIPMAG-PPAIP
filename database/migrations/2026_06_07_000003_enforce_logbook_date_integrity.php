<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->unique(['student_id', 'tanggal'], 'logbooks_student_date_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('approved_logbook_count');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedInteger('approved_logbook_count')->default(0);
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropUnique('logbooks_student_date_unique');
        });
    }
};
