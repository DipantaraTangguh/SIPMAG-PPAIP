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
                'name' => 'PPAIP',
                'email' => 'ppaip@bakrie.ac.id',
                'password' => $password,
                'role' => 'ppaip',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kaprodi demo untuk prodi Sistem Informasi.
            [
                'name' => 'Kaprodi SIF',
                'email' => 'kaprodi-sif@bakrie.ac.id',
                'password' => $password,
                'role' => 'kaprodi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kaprodi demo untuk prodi Informatika.
            [
                'name' => 'Kaprodi TIF',
                'email' => 'kaprodi-tif@bakrie.ac.id',
                'password' => $password,
                'role' => 'kaprodi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun DPM demo pertama.
            [
                'name' => 'Dospem SIF 1',
                'email' => 'dospem-sif-1@bakrie.ac.id',
                'password' => $password,
                'role' => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun DPM demo kedua.
            [
                'name' => 'Dospem SIF 2',
                'email' => 'dospem-sif-2@bakrie.ac.id',
                'password' => $password,
                'role' => 'dpm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Akun DPM demo ketiga.
            [
                'name' => 'Dospem TIF 2',
                'email' => 'dospem-tif-2@bakrie.ac.id',
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
                'name' => 'Dospeng TID 2',
                'email' => 'dospeng-tid-2@bakrie.ac.id',
                'password' => $password,
                'role' => 'dosen_penguji',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
