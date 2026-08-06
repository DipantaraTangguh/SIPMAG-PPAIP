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
            // Didaftarkan di sini supaya 100 mahasiswa demo ikut terbentuk
            // ulang setiap `migrate:fresh --seed`, bukan hilang begitu saja.
            DemoStudentsSeeder::class,
        ]);
    }
}
