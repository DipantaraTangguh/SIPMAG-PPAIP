<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InternshipCycleCompletionService
{
    public function __construct(
        private readonly DefenseAssessmentService $assessmentService,
        private readonly StudentStateMachine $stateMachine,
    ) {}

    public function canComplete(Student $student): bool
    {
        $student->loadMissing('sidangSubmission.assessments');
        $submission = $student->sidangSubmission;

        return $student->access_status === 'MenungguSidang'
            && $submission?->status === 'Scheduled'
            && $this->assessmentService->finalScore($submission) !== null;
    }

    public function complete(Student $student): float
    {
        return DB::transaction(function () use ($student): float {
            $lockedStudent = Student::query()
                ->lockForUpdate()
                ->findOrFail($student->id);
            $lockedStudent->load('sidangSubmission.assessments');
            $submission = $lockedStudent->sidangSubmission;

            if (
                $lockedStudent->access_status !== 'MenungguSidang'
                || $submission?->status !== 'Scheduled'
            ) {
                throw ValidationException::withMessages([
                    'cycle' => 'Siklus hanya dapat diselesaikan setelah sidang dijadwalkan.',
                ]);
            }

            $finalScore = $this->assessmentService->finalScore($submission);

            if ($finalScore === null) {
                throw ValidationException::withMessages([
                    'assessments' => 'Penilaian DPM, Penguji 1, dan Penguji 2 harus lengkap sebelum siklus diselesaikan.',
                ]);
            }

            $lockedStudent->fill([
                'dpm_id' => null,
                'is_independent' => false,
            ]);
            $this->stateMachine->transition($lockedStudent, 'SiklusSelesai');

            return $finalScore;
        });
    }
}
