<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Konfirmasi penerimaan magang non-wajib:
     * 1. Nilai enum baru 'MenungguKonfirmasi' pada students.access_status
     *    (MySQL-only; SQLite test membangun ulang dari migration create).
     * 2. Kolom loa_path di internship_cycles untuk bukti diterima (LoA).
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY access_status ENUM('Unverified','PendingReview','RejectedForm1','ApprovedForm1','HasApplication','HasDPM','LogbookComplete','MenungguSidang','SiklusSelesai','SelesaiNonWajib','MenungguKonfirmasi') NOT NULL DEFAULT 'Unverified'");
        }

        Schema::table('internship_cycles', function (Blueprint $table) {
            $table->string('loa_path')->nullable()->after('letter_grade');
        });
    }

    public function down(): void
    {
        Schema::table('internship_cycles', function (Blueprint $table) {
            $table->dropColumn('loa_path');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students MODIFY access_status ENUM('Unverified','PendingReview','RejectedForm1','ApprovedForm1','HasApplication','HasDPM','LogbookComplete','MenungguSidang','SiklusSelesai','SelesaiNonWajib') NOT NULL DEFAULT 'Unverified'");
        }
    }
};
