<?php

namespace App\Enums;

enum AssessorRole: string
{
    case Dpm = 'dpm';
    case ExaminerOne = 'penguji_1';
    case ExaminerTwo = 'penguji_2';

    /**
     * Label yang ditampilkan di panel penguji (kolom "Posisi Penilai").
     * Dipakai ExaminedSessionResource, termasuk untuk menentukan warna badge --
     * ubah di sini berarti ubah juga match warna di resource tersebut.
     */
    public function label(): string
    {
        return match ($this) {
            self::Dpm => 'DPM',
            self::ExaminerOne => 'Penguji 1',
            self::ExaminerTwo => 'Penguji 2',
        };
    }
}
