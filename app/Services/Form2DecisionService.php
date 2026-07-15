<?php

namespace App\Services;

use App\Models\Form2Submission;

/**
 * Satu-satunya tempat keputusan Form 2 dieksekusi (dipakai API controller DAN
 * action Filament PPAIP) supaya cabang wajib/non-wajib tidak pernah duplikat:
 * - wajib     : ApprovedForm1 → HasApplication (lanjut tahap DPM)
 * - non-wajib : → MenungguKonfirmasi (mahasiswa wajib konfirmasi + upload LoA)
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

        $jenis = $student->form1_data['jenisMagang'] ?? 'wajib';

        if ($jenis === 'non_wajib' && in_array($student->access_status, ['ApprovedForm1', 'HasApplication'], true)) {
            $this->stateMachine->transition($student, 'MenungguKonfirmasi');
        } elseif ($jenis !== 'non_wajib' && $student->access_status === 'ApprovedForm1') {
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
