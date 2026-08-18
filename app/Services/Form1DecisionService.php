<?php

namespace App\Services;

use App\Models\Student;

/**
 * Satu-satunya tempat keputusan Form 1 dieksekusi (dipakai endpoint API DAN
 * action Filament Kaprodi) supaya cabang wajib/non-wajib tidak pernah duplikat.
 *
 * - wajib     : berhenti di ApprovedForm1, lalu mahasiswa mencari tempat
 *               magang (Form 2 / portal) sebelum lanjut ke tahap DPM.
 * - non-wajib : langsung ke AwaitingConfirmation. Alurnya cuma Form 1 lalu
 *               form penerimaan, jadi mahasiswa bisa langsung mengunggah LoA
 *               tanpa Form 2, DPM, logbook, maupun sidang.
 */
class Form1DecisionService
{
    public function __construct(private readonly StudentStateMachine $stateMachine) {}

    public function approve(Student $student, ?int $lecturerId): void
    {
        $this->stateMachine->transition($student, 'ApprovedForm1', [
            'form1_rejection_reason' => null,
            'form1_approved_by' => $lecturerId,
            'form1_approved_at' => now(),
        ]);

        if ($this->isNonWajib($student)) {
            $this->stateMachine->transition($student, 'AwaitingConfirmation');
        }
    }

    /**
     * Status akhir setelah persetujuan, supaya response API tidak menebak.
     */
    public function resultingStatus(Student $student): string
    {
        return $this->isNonWajib($student) ? 'AwaitingConfirmation' : 'ApprovedForm1';
    }

    private function isNonWajib(Student $student): bool
    {
        return ($student->form1_data['jenisMagang'] ?? 'wajib') === 'non_wajib';
    }
}
