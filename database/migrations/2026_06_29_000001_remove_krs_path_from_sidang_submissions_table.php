<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sidang_submissions', 'krs_path')) {
            Schema::table('sidang_submissions', function (Blueprint $table) {
                $table->dropColumn('krs_path');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('sidang_submissions', 'krs_path')) {
            Schema::table('sidang_submissions', function (Blueprint $table) {
                $table->string('krs_path')->nullable()->after('poster_path');
            });
        }
    }
};
