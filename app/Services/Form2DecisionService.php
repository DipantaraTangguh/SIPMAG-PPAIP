<?php

namespace App\Services;

use App\Models\Form2Submission;

/**
 * Satu-satunya tempat keputusan Form 2 dieksekusi (dipakai API controller DAN
 * action Filament PPAIP): ApprovedForm1 → HasApplication, lanjut tahap DPM.
 *
 * Form 2 khusus magang wajib. Non-wajib tidak lewat sini sama sekali.
 */
class Form2DecisionService
{
    public function __construct(private readonly StudentStateMachine $stateMachine) {}

    public function approve(Form2Submission $submission): void
    {
        $submission->update([
            'status' => 'ApprovedForm2',
            'rejection_reason' => null,
        ]);

        $student = $submission->student;
        if (! $student) {
            return;
        }

        // Form 2 hanya ada di jalur wajib -- non-wajib ditolak sejak
        // Form2Controller@store, jadi tidak ada cabang non-wajib di sini.
        if ($student->access_status === 'ApprovedForm1') {
            $this->stateMachine->transition($student, 'HasApplication');
        }
    }

    public function reject(Form2Submission $submission, string $reason): void
    {
        $submission->update([
            'status' => 'RejectedForm2',
            'rejection_reason' => $reason,
        ]);
    }
}
