<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // user_id 7-11 = Mahasiswa users (from UserSeeder)
        // dpm_id references lecturers table (id 3 = Dita, id 4 = Ahmad Fauzi)

        DB::table('students')->insert([
            // Tangguh — Unverified (fresh start, main demo user)
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
