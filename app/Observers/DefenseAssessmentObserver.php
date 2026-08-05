<?php

namespace App\Observers;

use App\Models\DefenseAssessment;
use App\Models\Student;
use App\Services\InternshipCycleCompletionService;

class DefenseAssessmentObserver
{
    /**
     * Nilai akhir terbit otomatis begitu DPM + 2 penguji selesai menilai.
     * Tidak ada langkah manual "Selesaikan Siklus" lagi.
     *
     * Hanya jalur magang wajib yang punya sidang (non-wajib berhenti di
     * Form 2), jadi cek canComplete() sudah cukup untuk membatasi jalur.
     */
    public function saved(DefenseAssessment $assessment): void
    {
        // Query ulang supaya relasi assessments-nya ikut baris yang baru
        // disimpan, bukan versi lama yang mungkin masih ke-cache.
        $student = Student::query()
            ->whereHas('sidangSubmission', fn ($query) => $query->whereKey($assessment->defense_submission_id))
            ->first();

        if (! $student) {
            return;
        }

        $service = app(InternshipCycleCompletionService::class);

        if ($service->canComplete($student)) {
            $service->complete($student);
        }
    }
}
