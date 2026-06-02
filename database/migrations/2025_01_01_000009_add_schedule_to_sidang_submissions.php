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
            $table->string('dosen_penguji_1')->nullable()->after('room');
            $table->string('dosen_penguji_2')->nullable()->after('dosen_penguji_1');
            $table->foreignId('scheduled_by')->nullable()->after('dosen_penguji_2')->constrained('lecturers')->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable()->after('scheduled_by');
        });
    }

    public function down(): void
    {
        Schema::table('sidang_submissions', function (Blueprint $table) {
            $table->dropForeign(['scheduled_by']);
            $table->dropColumn([
                'status',
                'scheduled_date',
                'scheduled_time',
                'room',
                'dosen_penguji_1',
                'dosen_penguji_2',
                'scheduled_by',
                'scheduled_at',
            ]);
        });
    }
};
