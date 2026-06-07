<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sidang_submissions', function (Blueprint $table) {
            $table->string('foto_kegiatan_1_path')->nullable()->after('poster_path');
            $table->string('foto_kegiatan_2_path')->nullable()->after('foto_kegiatan_1_path');
        });
    }

    public function down(): void
    {
        Schema::table('sidang_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'foto_kegiatan_1_path',
                'foto_kegiatan_2_path',
            ]);
        });
    }
};
