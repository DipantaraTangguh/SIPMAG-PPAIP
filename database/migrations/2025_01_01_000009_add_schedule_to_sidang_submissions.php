<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sidang_submissions', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Scheduled'])->default('Pending')->after('krs_path');
            $table->date('scheduled_date')->nullable()->after('status');
            $table->string('scheduled_time', 10)->nullable()->after('scheduled_date');
            $table->string('room', 100)->nullable()->after('scheduled_time');
            $table->foreignId('dosen_penguji_1_id')->nullable()->after('room')->constrained('lecturers')->nullOnDelete();
            $table->foreignId('dosen_penguji_2_id')->nullable()->after('dosen_penguji_1_id')->constrained('lecturers')->nullOnDelete();
            $table->foreignId('scheduled_by')->nullable()->after('dosen_penguji_2_id')->constrained('lecturers')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable()->after('scheduled_by');
        });
    }

    public function down(): void
    {
        Schema::table('sidang_submissions', function (Blueprint $table) {
            foreach (['dosen_penguji_1_id', 'dosen_penguji_2_id', 'scheduled_by'] as $column) {
                if (Schema::hasColumn('sidang_submissions', $column)) {
                    $table->dropForeign([$column]);
                }
            }

            $columns = array_values(array_filter([
                'status',
                'scheduled_date',
                'scheduled_time',
                'room',
                Schema::hasColumn('sidang_submissions', 'dosen_penguji_1_id') ? 'dosen_penguji_1_id' : null,
                Schema::hasColumn('sidang_submissions', 'dosen_penguji_2_id') ? 'dosen_penguji_2_id' : null,
                Schema::hasColumn('sidang_submissions', 'dosen_penguji_1') ? 'dosen_penguji_1' : null,
                Schema::hasColumn('sidang_submissions', 'dosen_penguji_2') ? 'dosen_penguji_2' : null,
                'scheduled_by',
                'scheduled_at',
            ]));

            $table->dropColumn($columns);
        });
    }
};
