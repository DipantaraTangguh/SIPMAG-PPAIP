<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah nilai enum 'SelesaiNonWajib' ke students.access_status.
     *
     * Hanya relevan untuk MySQL yang sudah terlanjur migrate dengan enum lama.
     * Pada SQLite (test) enum = kolom teks, dan daftar nilai sudah disertakan
     * langsung di migration create students, jadi tidak perlu diubah di sini.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE students MODIFY access_status ENUM(".$this->values().") NOT NULL DEFAULT 'Unverified'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE students MODIFY access_status ENUM(".$this->valuesWithout('SelesaiNonWajib').") NOT NULL DEFAULT 'Unverified'");
    }

    private function values(): string
    {
        return "'Unverified','PendingReview','RejectedForm1','ApprovedForm1','HasApplication','HasDPM','LogbookComplete','MenungguSidang','SiklusSelesai','SelesaiNonWajib'";
    }

    private function valuesWithout(string $value): string
    {
        return "'Unverified','PendingReview','RejectedForm1','ApprovedForm1','HasApplication','HasDPM','LogbookComplete','MenungguSidang','SiklusSelesai'";
    }
};
