<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LecturerSeeder::class,
            StudentSeeder::class,
            InternshipSeeder::class,
            // Paling akhir: peserta uji coba dan Kaprodi asli tiap prodi.
            // Urutannya penting -- seeder ini memakai ulang baris Kaprodi
            // contoh dari LecturerSeeder supaya tidak ada prodi yang punya
            // dua Kaprodi sekaligus.
            TestingAccountsSeeder::class,
        ]);
    }
}
