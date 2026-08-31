<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Logbook yang ditolak tidak lagi diperbaiki; mahasiswa mengirim entri baru
 * untuk tanggal yang sama. Indeks unik lama (student_id, tanggal) memblokir
 * itu, jadi diganti indeks yang hanya berlaku untuk baris "aktif".
 *
 * Kolom bantunya STORED supaya MySQL yang menghitung, bukan aplikasi:
 * bernilai tanggal selama barisnya belum dihapus dan belum ditolak, selain
 * itu NULL. Beberapa NULL tidak dianggap bentrok oleh indeks unik MySQL,
 * sehingga satu tanggal boleh punya berapa pun baris ditolak tetapi tetap
 * hanya satu baris yang menunggu review atau disetujui.
 *
 * Ini sekaligus melepas sekat yang selama ini tak terlihat: reset siklus
 * hanya men-soft-delete logbook, dan indeks lama ikut menghitung baris
 * terhapus -- sehingga tanggal yang sama tidak bisa dipakai lagi di siklus
 * berikutnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropUnique('logbooks_student_date_unique');
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->date('tanggal_aktif')
                ->nullable()
                ->storedAs("CASE WHEN deleted_at IS NULL AND status <> 'Rejected' THEN tanggal END");

            $table->unique(['student_id', 'tanggal_aktif'], 'logbooks_student_active_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropUnique('logbooks_student_active_date_unique');
            $table->dropColumn('tanggal_aktif');
        });

        Schema::table('logbooks', function (Blueprint $table) {
            $table->unique(['student_id', 'tanggal'], 'logbooks_student_date_unique');
        });
    }
};
