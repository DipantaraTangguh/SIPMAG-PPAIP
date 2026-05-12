<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        DB::table('users')->insert([
            // PPAIP (admin)
            [
                'name'     => 'Dr. Rizki Maryam Astuti, M.Si.',
                'email'    => 'ppaip@bakrie.ac.id',
                'password' => $password,
                'role'     => 'ppaip',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kaprodi — Sistem Informasi
            [
                'name'     => 'Prof. Siti Wati',
                'email'    => 'siti.wati@bakrie.ac.id',
                'password' => $password,
                'role'     => 'kaprodi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kaprodi — Informatika
            [
                'name'     => 'Dr. Budi Santoso, M.T.',
                'email'    => 'budi.santoso@bakrie.ac.id',
                'password' => $password,
                'role'     => 'kaprodi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // DPM 1
            [
                'name'     => 'Dita Nurmadewi, S.Kom.',
                'email'    => 'dita.nurmadewi@bakrie.ac.id',
                'password' => $password,
                'role'     => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // DPM 2
            [
                'name'     => 'Dr. Ahmad Fauzi, M.T.',
                'email'    => 'ahmad.fauzi@bakrie.ac.id',
                'password' => $password,
                'role'     => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // DPM 3
            [
                'name'     => 'Siti Aminah, M.Kom.',
                'email'    => 'siti.aminah@bakrie.ac.id',
                'password' => $password,
                'role'     => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mahasiswa 1 — Tangguh (main demo user, Unverified)
            [
                'name'     => 'Tangguh Dipantara',
                'email'    => 'tangguh@student.bakrie.ac.id',
                'password' => $password,
                'role'     => 'mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mahasiswa 2 — ApprovedForm1
            [
                'name'     => 'Rina Amelia',
                'email'    => 'rina.amelia@student.bakrie.ac.id',
                'password' => $password,
                'role'     => 'mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mahasiswa 3 — HasApplication
            [
                'name'     => 'Farhan Pratama',
                'email'    => 'farhan.pratama@student.bakrie.ac.id',
                'password' => $password,
                'role'     => 'mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mahasiswa 4 — HasDPM
            [
                'name'     => 'Dewi Kartika',
                'email'    => 'dewi.kartika@student.bakrie.ac.id',
                'password' => $password,
                'role'     => 'mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mahasiswa 5 — MenungguSidang
            [
                'name'     => 'Bayu Aditya',
                'email'    => 'bayu.aditya@student.bakrie.ac.id',
                'password' => $password,
                'role'     => 'mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
