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
                'access_status'          => 'Unverified',
                'is_independent'         => false,
                'form1_data'             => null,
                'form1_pdf_path'         => null,
                'form1_rejection_reason' => null,
                'approved_logbook_count' => 0,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            // Rina — ApprovedForm1 (ready to apply)
            [
                'user_id'                => 8,
                'dpm_id'                 => null,
                'nim'                    => '1101214231',
                'name'                   => 'Rina Amelia',
                'study_program'          => 'Sistem Informasi',
                'email'                  => 'rina.amelia@student.bakrie.ac.id',
                'access_status'          => 'ApprovedForm1',
                'is_independent'         => false,
                'form1_data'             => json_encode([
                    'semester'       => 'Ganjil 2024/2025',
                    'jumlahSKS'      => '120',
                    'ipk'            => '3.75',
                    'skemaMagang'    => 'Mitra',
                    'topikMagang'    => 'Data Analytics',
                    'outputTarget'   => 'Laporan Analisis Data',
                ]),
                'form1_pdf_path'         => null,
                'form1_rejection_reason' => null,
                'approved_logbook_count' => 0,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            // Farhan — HasApplication (applied to a vacancy)
            [
                'user_id'                => 9,
                'dpm_id'                 => null,
                'nim'                    => '1101214232',
                'name'                   => 'Farhan Pratama',
                'study_program'          => 'Informatika',
                'email'                  => 'farhan.pratama@student.bakrie.ac.id',
                'access_status'          => 'HasApplication',
                'is_independent'         => false,
                'form1_data'             => json_encode([
                    'semester'       => 'Ganjil 2024/2025',
                    'jumlahSKS'      => '118',
                    'ipk'            => '3.60',
                    'skemaMagang'    => 'Mitra',
                    'topikMagang'    => 'Software Engineering',
                    'outputTarget'   => 'Prototype Aplikasi',
                ]),
                'form1_pdf_path'         => null,
                'form1_rejection_reason' => null,
                'approved_logbook_count' => 0,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            // Dewi — HasDPM (DPM assigned, filling logbook)
            [
                'user_id'                => 10,
                'dpm_id'                 => 3, // Dita Nurmadewi (lecturer id 3)
                'nim'                    => '1101214233',
                'name'                   => 'Dewi Kartika',
                'study_program'          => 'Sistem Informasi',
                'email'                  => 'dewi.kartika@student.bakrie.ac.id',
                'access_status'          => 'HasDPM',
                'is_independent'         => false,
                'form1_data'             => json_encode([
                    'semester'       => 'Ganjil 2024/2025',
                    'jumlahSKS'      => '124',
                    'ipk'            => '3.84',
                    'skemaMagang'    => 'Mitra',
                    'topikMagang'    => 'UI/UX Design',
                    'outputTarget'   => 'Desain Prototipe',
                ]),
                'form1_pdf_path'         => null,
                'form1_rejection_reason' => null,
                'approved_logbook_count' => 3,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
            // Bayu — MenungguSidang (waiting for sidang)
            [
                'user_id'                => 11,
                'dpm_id'                 => 4, // Dr. Ahmad Fauzi (lecturer id 4)
                'nim'                    => '1101214234',
                'name'                   => 'Bayu Aditya',
                'study_program'          => 'Informatika',
                'email'                  => 'bayu.aditya@student.bakrie.ac.id',
                'access_status'          => 'MenungguSidang',
                'is_independent'         => false,
                'form1_data'             => json_encode([
                    'semester'       => 'Ganjil 2024/2025',
                    'jumlahSKS'      => '130',
                    'ipk'            => '3.92',
                    'skemaMagang'    => 'Mitra',
                    'topikMagang'    => 'Backend Engineering',
                    'outputTarget'   => 'Laporan Sistem',
                ]),
                'form1_pdf_path'         => null,
                'form1_rejection_reason' => null,
                'approved_logbook_count' => 6,
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
        ]);
    }
}
