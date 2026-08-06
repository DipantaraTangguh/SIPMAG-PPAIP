<?php

namespace App\Support;

/**
 * Daftar program studi Universitas Bakrie. Satu-satunya sumber kebenaran
 * supaya opsi di Filament, aturan validasi, dan seeder tidak pernah beda.
 */
class StudyProgram
{
    public const ALL = [
        'Manajemen',
        'Akuntansi',
        'Ilmu Politik',
        'Ilmu Komunikasi',
        'Informatika',
        'Sistem Informasi',
        'Teknik Industri',
        'Teknik Sipil',
        'Ilmu & Teknologi Pangan',
        'Teknik Lingkungan',
    ];

    /**
     * Opsi untuk field select Filament (value === label).
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(self::ALL, self::ALL);
    }
}
