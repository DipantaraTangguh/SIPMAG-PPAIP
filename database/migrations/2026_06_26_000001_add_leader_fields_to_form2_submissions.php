<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form2_submissions', function (Blueprint $table) {
            $table->string('nama_pimpinan')->nullable()->after('company_name');
            $table->string('jabatan_pimpinan')->nullable()->after('nama_pimpinan');
        });
    }

    public function down(): void
    {
        Schema::table('form2_submissions', function (Blueprint $table) {
            $table->dropColumn(['nama_pimpinan', 'jabatan_pimpinan']);
        });
    }
};
