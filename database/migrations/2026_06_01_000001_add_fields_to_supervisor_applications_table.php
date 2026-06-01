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
        Schema::table('supervisor_applications', function (Blueprint $table) {
            $table->string('nama_praktisi')->nullable();
            $table->string('jabatan_praktisi')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            $table->date('mulai_magang')->nullable();
            $table->date('selesai_magang')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisor_applications', function (Blueprint $table) {
            $table->dropColumn([
                'nama_praktisi',
                'jabatan_praktisi',
                'no_telepon',
                'email',
                'mulai_magang',
                'selesai_magang'
            ]);
        });
    }
};
