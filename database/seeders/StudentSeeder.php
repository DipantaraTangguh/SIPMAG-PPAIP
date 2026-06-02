<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // ID ini ngikut urutan user demo di UserSeeder.
        // DPM demo ambil dari lecturer seed, bukan user langsung.

        DB::table('students')->insert([
            // User demo utama sengaja mulai dari awal flow.
            [
                'user_id'                => 7,
                'dpm_id'                 => null,
                'nim'                    => '1101214230',
                'name'                   => 'Tangguh Dipantara',
                'study_program'          => 'Sistem Informasi',
                'email'                  => 'tangguh@student.bakrie.ac.id',
                'semester'               => '6',
                'tahun_akademik'         => '2024/2025',
                'jumlah_sks'             => '120',
                'ipk'                    => '3.75',
                'access_status'          => 'Unverified',
                'is_independent'         => false,
                'form1_data'             => null,
                'form1_pdf_path'         => null,
                'form1_rejection_reason' => null,
                'approved_logbook_count' => 0,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
        ]);
    }
}
