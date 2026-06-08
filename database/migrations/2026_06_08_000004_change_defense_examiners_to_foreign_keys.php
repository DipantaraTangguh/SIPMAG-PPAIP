<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sidang_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('sidang_submissions', 'dosen_penguji_1_id')) {
                $table->foreignId('dosen_penguji_1_id')->nullable()->after('room')->constrained('lecturers')->nullOnDelete();
            }

            if (! Schema::hasColumn('sidang_submissions', 'dosen_penguji_2_id')) {
                $table->foreignId('dosen_penguji_2_id')->nullable()->after('dosen_penguji_1_id')->constrained('lecturers')->nullOnDelete();
            }
        });

        if (
            Schema::hasColumn('sidang_submissions', 'dosen_penguji_1')
            || Schema::hasColumn('sidang_submissions', 'dosen_penguji_2')
        ) {
            $this->copyExaminerNamesToIds();

            Schema::table('sidang_submissions', function (Blueprint $table) {
                $columns = [];

                if (Schema::hasColumn('sidang_submissions', 'dosen_penguji_1')) {
                    $columns[] = 'dosen_penguji_1';
                }

                if (Schema::hasColumn('sidang_submissions', 'dosen_penguji_2')) {
                    $columns[] = 'dosen_penguji_2';
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('sidang_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('sidang_submissions', 'dosen_penguji_1')) {
                $table->string('dosen_penguji_1')->nullable()->after('room');
            }

            if (! Schema::hasColumn('sidang_submissions', 'dosen_penguji_2')) {
                $table->string('dosen_penguji_2')->nullable()->after('dosen_penguji_1');
            }
        });

        $this->copyExaminerIdsToNames();

        Schema::table('sidang_submissions', function (Blueprint $table) {
            foreach (['dosen_penguji_1_id', 'dosen_penguji_2_id'] as $column) {
                if (Schema::hasColumn('sidang_submissions', $column)) {
                    $table->dropForeign([$column]);
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function copyExaminerNamesToIds(): void
    {
        $lecturers = DB::table('lecturers')->pluck('id', 'lecturer_name');
        $submissions = DB::table('sidang_submissions')
            ->select(['id', 'dosen_penguji_1', 'dosen_penguji_2'])
            ->get();

        foreach ($submissions as $submission) {
            DB::table('sidang_submissions')
                ->where('id', $submission->id)
                ->update([
                    'dosen_penguji_1_id' => $lecturers[$submission->dosen_penguji_1] ?? null,
                    'dosen_penguji_2_id' => $lecturers[$submission->dosen_penguji_2] ?? null,
                ]);
        }
    }

    private function copyExaminerIdsToNames(): void
    {
        $lecturers = DB::table('lecturers')->pluck('lecturer_name', 'id');
        $submissions = DB::table('sidang_submissions')
            ->select(['id', 'dosen_penguji_1_id', 'dosen_penguji_2_id'])
            ->get();

        foreach ($submissions as $submission) {
            DB::table('sidang_submissions')
                ->where('id', $submission->id)
                ->update([
                    'dosen_penguji_1' => $lecturers[$submission->dosen_penguji_1_id] ?? null,
                    'dosen_penguji_2' => $lecturers[$submission->dosen_penguji_2_id] ?? null,
                ]);
        }
    }
};
