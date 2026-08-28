<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dukungan akun Staff Prodi: orang yang bekerja atas nama Kaprodi.
 *
 * Staff bukan dosen -- mereka tenaga kependidikan dan tidak punya NIDN --
 * jadi sengaja TIDAK diberi baris di tabel lecturers. Kolom nidn di sana
 * unik dan wajib diisi, dan tabel itulah yang memasok nama serta NIDN ke
 * surat resmi sekaligus mengisi daftar calon DPM dan dosen penguji;
 * menaruh staff di sana berarti menaruh NIDN karangan di jalur dokumen
 * resmi sekaligus membuat mereka bisa dipilih sebagai penguji sidang.
 *
 * Karena itu program studi staff disimpan langsung di tabel users, dan
 * ketiadaan baris dosen itulah yang dipakai untuk mengenali mereka.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('study_program')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('study_program');
        });
    }
};
