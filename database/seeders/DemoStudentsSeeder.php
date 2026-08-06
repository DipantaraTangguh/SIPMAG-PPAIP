<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Support\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 100 mahasiswa demo: 10 orang untuk tiap-tiap 10 program studi.
 *
 * NIM berpola 2026PPNN -- PP = nomor urut prodi (01-10), NN = nomor urut
 * mahasiswa dalam prodi itu (01-10). Semua memakai kata sandi 'password'.
 *
 * Separuh pertama tiap prodi (NN 01-05) sengaja dibiarkan Unverified supaya
 * bisa dipakai menguji alur dari Form 1; separuh kedua (NN 06-10) langsung
 * ApprovedForm1 supaya bisa langsung melamar lowongan mitra dan menguji
 * pembatasan program studi.
 *
 * Idempotent: dijalankan ulang tidak menggandakan data.
 */
class DemoStudentsSeeder extends Seeder
{
    private const PER_PROGRAM = 10;

    /** NN <= batas ini dibiarkan Unverified. */
    private const UNVERIFIED_UNTIL = 5;

    private const FIRST_NAMES = [
        'Andi', 'Bila', 'Citra', 'Dimas', 'Eka', 'Farhan', 'Gita', 'Hana',
        'Irfan', 'Joko', 'Kirana', 'Lukman', 'Maya', 'Nadia', 'Oscar', 'Putri',
        'Rangga', 'Sari', 'Taufik', 'Umar',
    ];

    private const LAST_NAMES = [
        'Pratama', 'Wijaya', 'Lestari', 'Hidayat', 'Nugroho', 'Anggraini',
        'Saputra', 'Maulana', 'Kusuma', 'Ramadhan',
    ];

    public function run(): void
    {
        $password = Hash::make('password');

        foreach (array_values(StudyProgram::ALL) as $programIndex => $program) {
            $programNumber = str_pad((string) ($programIndex + 1), 2, '0', STR_PAD_LEFT);
            $slug = Str::slug($program, '.');

            for ($i = 1; $i <= self::PER_PROGRAM; $i++) {
                $sequence = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $nim = "2026{$programNumber}{$sequence}";
                $email = "{$slug}.{$sequence}@student.bakrie.ac.id";

                $name = self::FIRST_NAMES[($programIndex * self::PER_PROGRAM + $i) % count(self::FIRST_NAMES)]
                    .' '.self::LAST_NAMES[$i % count(self::LAST_NAMES)];

                $user = User::firstOrCreate(
                    ['email' => $email],
                    ['name' => $name, 'password' => $password, 'role' => 'mahasiswa'],
                );

                $isUnverified = $i <= self::UNVERIFIED_UNTIL;

                $student = Student::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'user_id' => $user->id,
                        'name' => $name,
                        'study_program' => $program,
                        'email' => $email,
                        'semester' => 6,
                        'tahun_akademik' => '2026/2027',
                        'jumlah_sks' => 120,
                        'ipk' => 3.00 + (($i % 10) / 10),
                        'is_independent' => false,
                        'form1_data' => $isUnverified ? null : [
                            'semester' => 6,
                            'jumlahSKS' => 120,
                            'ipk' => 3.00 + (($i % 10) / 10),
                            'jenisMagang' => 'wajib',
                            'skemaMagang' => 'Magang Perusahaan',
                            'topikMagang' => 'Magang di bidang '.$program,
                            'outputTarget' => 'Laporan Akhir',
                        ],
                    ],
                );

                // access_status sengaja tidak fillable (dikuasai StudentStateMachine),
                // jadi harus di-set eksplisit di luar updateOrCreate.
                $student->forceFill([
                    'access_status' => $isUnverified ? 'Unverified' : 'ApprovedForm1',
                ])->save();
            }
        }
    }
}
