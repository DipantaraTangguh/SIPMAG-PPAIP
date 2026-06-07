<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        // 1. users table: add soft deletes
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 2. students table: drop user_id foreign key, re-add as restrictOnDelete, add soft deletes
        if (!Schema::hasColumn('students', 'deleted_at')) {
            Schema::table('students', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['user_id']);
                    $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
                }
                $table->softDeletes();
            });
        }

        // 3. applications table: drop student_id foreign key, re-add as restrictOnDelete, add soft deletes
        if (!Schema::hasColumn('applications', 'deleted_at')) {
            Schema::table('applications', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
                }
                $table->softDeletes();
            });
        }

        // 4. form2_submissions table: drop student_id foreign key, re-add as restrictOnDelete, add soft deletes
        if (!Schema::hasColumn('form2_submissions', 'deleted_at')) {
            Schema::table('form2_submissions', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
                }
                $table->softDeletes();
            });
        }

        // 5. supervisor_applications table: drop student_id foreign key, re-add as restrictOnDelete, add soft deletes
        if (!Schema::hasColumn('supervisor_applications', 'deleted_at')) {
            Schema::table('supervisor_applications', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
                }
                $table->softDeletes();
            });
        }

        // 6. logbooks table: drop student_id foreign key, re-add as restrictOnDelete, add soft deletes
        if (!Schema::hasColumn('logbooks', 'deleted_at')) {
            Schema::table('logbooks', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
                }
                $table->softDeletes();
            });
        }

        // 7. sidang_submissions table: drop student_id foreign key, re-add as restrictOnDelete, add soft deletes
        if (!Schema::hasColumn('sidang_submissions', 'deleted_at')) {
            Schema::table('sidang_submissions', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->restrictOnDelete();
                }
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        // 1. sidang_submissions table
        if (Schema::hasColumn('sidang_submissions', 'deleted_at')) {
            Schema::table('sidang_submissions', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
                }
                $table->dropSoftDeletes();
            });
        }

        // 2. logbooks table
        if (Schema::hasColumn('logbooks', 'deleted_at')) {
            Schema::table('logbooks', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
                }
                $table->dropSoftDeletes();
            });
        }

        // 3. supervisor_applications table
        if (Schema::hasColumn('supervisor_applications', 'deleted_at')) {
            Schema::table('supervisor_applications', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
                }
                $table->dropSoftDeletes();
            });
        }

        // 4. form2_submissions table
        if (Schema::hasColumn('form2_submissions', 'deleted_at')) {
            Schema::table('form2_submissions', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
                }
                $table->dropSoftDeletes();
            });
        }

        // 5. applications table
        if (Schema::hasColumn('applications', 'deleted_at')) {
            Schema::table('applications', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['student_id']);
                    $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
                }
                $table->dropSoftDeletes();
            });
        }

        // 6. students table
        if (Schema::hasColumn('students', 'deleted_at')) {
            Schema::table('students', function (Blueprint $table) use ($isSqlite) {
                if (!$isSqlite) {
                    $table->dropForeign(['user_id']);
                    $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                }
                $table->dropSoftDeletes();
            });
        }

        // 7. users table
        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
