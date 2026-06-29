<?php

namespace App\Enums;

enum AssessorRole: string
{
    case Dpm = 'dpm';
    case ExaminerOne = 'penguji_1';
    case ExaminerTwo = 'penguji_2';

    /**
     * Display label used in the examiner-facing UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Dpm => 'DPM',
            self::ExaminerOne => 'Examiner 1',
            self::ExaminerTwo => 'Examiner 2',
        };
    }
}
