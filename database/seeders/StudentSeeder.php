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
                'user_id' => 7,
                'dpm_id' => null,
                'nim' => '1101214230',
                'name' => 'Tangguh Dipantara',
                'study_program' => 'Sistem Informasi',
                'email' => 'tangguh@student.bakrie.ac.id',
                'semester' => 6,
                'tahun_akademik' => '2026/2027',
                'jumlah_sks' => 120,
                'ipk' => 3.75,
                'access_status' => 'Unverified',
                'is_independent' => false,
                'form1_data' => null,
                'form1_pdf_path' => null,
                'form1_rejection_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $logbookStudentId = DB::table('students')->insertGetId([
            // User demo khusus untuk tes logbook langsung.
            'user_id' => 8,
            'dpm_id' => 3,
            'nim' => '1101214231',
            'name' => 'Raka Logbook Pratama',
            'study_program' => 'Sistem Informasi',
            'email' => 'raka.logbook@student.bakrie.ac.id',
            'semester' => 6,
            'tahun_akademik' => '2026/2027',
            'jumlah_sks' => 120,
            'ipk' => 3.75,
            'access_status' => 'HasDPM',
            'is_independent' => false,
            'form1_data' => null,
            'form1_pdf_path' => null,
            'form1_rejection_reason' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('supervisor_applications')->insert([
            'student_id' => $logbookStudentId,
            'company_name' => 'PT Contoh Indonesia',
            'company_contact' => 'Budi Santoso - 08123456789',
            'nama_praktisi' => 'Budi Santoso',
            'jabatan_praktisi' => 'Engineering Manager',
            'no_telepon' => '08123456789',
            'email' => 'budi@example.test',
            'mulai_magang' => now()->subDays(7)->toDateString(),
            'selesai_magang' => now()->addMonths(3)->toDateString(),
            'loa_path' => 'loa/demo-loa.pdf',
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
