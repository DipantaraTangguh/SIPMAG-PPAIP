<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('mahasiswa', 'kaprodi', 'dpm', 'ppaip', 'dosen_penguji') NOT NULL DEFAULT 'mahasiswa'");
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'dosen_penguji')
            ->update(['role' => 'dpm']);

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('mahasiswa', 'kaprodi', 'dpm', 'ppaip') NOT NULL DEFAULT 'mahasiswa'");
    }
};
