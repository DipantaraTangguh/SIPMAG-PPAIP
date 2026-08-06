<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ganti 'bidang' (teks bebas, mis. "Software Engineering") dengan daftar
 * program studi yang boleh melamar lowongan mitra tersebut.
 *
 * Nilai bidang lama sengaja tidak dipindahkan: isinya bukan nama prodi.
 * Lowongan lama jadi study_programs = null, artinya terbuka untuk semua prodi
 * sampai PPAIP mengisinya lewat panel admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->json('study_programs')->nullable()->after('location');
        });

        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn('bidang');
        });
    }

    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->string('bidang')->nullable()->after('location');
        });

        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn('study_programs');
        });
    }
};
