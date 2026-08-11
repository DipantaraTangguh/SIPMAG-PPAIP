<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Logo perusahaan untuk lowongan mitra. Disimpan di disk 'public' (bukan
 * 'local' seperti LoA/CV) karena ini gambar branding yang memang untuk
 * ditampilkan, jadi bisa diakses lewat URL biasa tanpa rute streaming.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};
