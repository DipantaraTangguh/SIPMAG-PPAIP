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
            // Akun admin PPAIP buat manage data lintas prodi.
            [
                'name' => 'Dr. Rizki Maryam Astuti, M.Si.',
                'email' => 'ppaip@bakrie.ac.id',
                'password' => $password,
                'role' => 'ppaip',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kaprodi demo untuk prodi Sistem Informasi.
            [
                'name' => 'Prof. Dr. Hoga Saragih, ST, MT',
                'email' => 'hoga.saragih@bakrie.ac.id',
                'password' => $password,
                'role' => 'kaprodi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kaprodi demo untuk prodi Informatika.
            [
                'name' => 'Dr. Budi Santoso, M.T.',
                'email' => 'budi.santoso@bakrie.ac.id',
                'password' => $password,
                'role' => 'kaprodi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun DPM demo pertama.
            [
                'name' => 'Dita Nurmadewi, S.Kom.',
                'email' => 'dita.nurmadewi@bakrie.ac.id',
                'password' => $password,
                'role' => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun DPM demo kedua.
            [
                'name' => 'Dr. Ahmad Fauzi, M.T.',
                'email' => 'ahmad.fauzi@bakrie.ac.id',
                'password' => $password,
                'role' => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun DPM demo ketiga.
            [
                'name' => 'Siti Aminah, M.Kom.',
                'email' => 'siti.aminah@bakrie.ac.id',
                'password' => $password,
                'role' => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mahasiswa demo utama, mulai dari status Unverified.
            [
                'name' => 'Tangguh Dipantara',
                'email' => 'tangguh@student.bakrie.ac.id',
                'password' => $password,
                'role' => 'mahasiswa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun dosen penguji demo.
            [
                'name' => 'Dr. Maya Puspita, M.Kom.',
                'email' => 'maya.puspita@bakrie.ac.id',
                'password' => $password,
                'role' => 'dosen_penguji',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
