<?php

namespace App\Services;

use App\Models\InternshipCycle;
use App\Models\Student;

/**
 * Merekam snapshot satu siklus magang yang selesai ke tabel riwayat
 * (internship_cycles). Dipanggil tepat sekali per penyelesaian siklus:
 * - magang wajib  : dari InternshipCycleCompletionService::complete()
 * - magang non-wajib: dari Form2Controller::approve() saat masuk ElectiveCompleted
 */
class InternshipCycleSnapshotService
{
    public function __construct(private readonly DefenseAssessmentService $assessmentService) {}

    /**
     * @param  array<string, mixed>  $overrides  Data konfirmasi mahasiswa (tempat
     *                                           diterima, periode, LoA) yang lebih
     *                                           akurat daripada tebakan sistem.
     */
    public function record(Student $student, array $overrides = []): InternshipCycle
    {
        $form1 = $student->form1_data ?? [];
        $jenis = ($form1['jenisMagang'] ?? 'wajib') === 'non_wajib' ? 'non_wajib' : 'wajib';

        [$company, $address, $leader, $start, $end] = $this->placementSnapshot($student);
        [$finalScore, $letterGrade] = $jenis === 'wajib'
            ? $this->assessmentSnapshot($student)
            : [null, null];

        $cycleNumber = $student->internshipCycles()->count() + 1;

        return $student->internshipCycles()->create(array_merge([
            'cycle_number'     => $cycleNumber,
            'jenis_magang'     => $jenis,
            'outcome_status'   => $jenis === 'wajib' ? 'CycleCompleted' : 'ElectiveCompleted',
            'nim'              => $student->nim,
            'nama'             => $student->name,
            'study_program'    => $student->study_program,
            'semester'         => $student->semester,
            'ipk'              => $student->ipk,
            'skema_magang'     => $form1['skemaMagang'] ?? null,
            'topik_magang'     => $form1['topikMagang'] ?? null,
            'output_target'    => $form1['outputTarget'] ?? null,
            'company_name'     => $company,
            'alamat_perusahaan' => $address,
            'nama_pimpinan'    => $leader,
            'tanggal_mulai'    => $start,
            'tanggal_selesai'  => $end,
            'final_score'      => $finalScore,
            'letter_grade'     => $letterGrade,
            'started_at'       => $student->form1_approved_at,
            'completed_at'     => now(),
        ], $overrides));
    }

    /**
     * Tempat & periode magang.
     *
     * Pengajuan pembimbing magang didahulukan karena itulah satu-satunya
     * berkas yang menyatakan mahasiswa DITERIMA di mana -- mahasiswa
     * mengisinya sambil mengunggah LoA. Form 2 hanya surat pengantar ke
     * perusahaan yang dituju, jadi perusahaannya belum tentu yang akhirnya
     * menerima; memakai Form 2 lebih dulu membuat rekap mencatat tempat yang
     * salah untuk mahasiswa wajib yang diterima di perusahaan lain.
     *
     * Form 2 tetap dipakai sebagai sumber untuk magang non-wajib, yang memang
     * tidak pernah punya pengajuan pembimbing (SupervisorController menolak
     * mereka) dan berhenti di Form 2.
     *
     * Lamaran mitra sengaja tidak dilihat sama sekali: statusnya tidak pernah
     * menyatakan diterima atau tidak, hanya minat mahasiswa.
     *
     * @return array{0:?string,1:?string,2:?string,3:mixed,4:mixed}
     */
    private function placementSnapshot(Student $student): array
    {
        $supervisor = $student->supervisorApplication;

        if ($supervisor) {
            return [
                $supervisor->company_name,
                null,
                $supervisor->nama_praktisi,
                $supervisor->mulai_magang,
                $supervisor->selesai_magang,
            ];
        }

        $form2 = $student->form2Submissions()
            ->where('status', 'ApprovedForm2')
            ->latest('submitted_at')
            ->first();

        if ($form2) {
            return [
                $form2->company_name,
                $form2->alamat_perusahaan,
                $form2->nama_pimpinan,
                $form2->tanggal_mulai,
                $form2->tanggal_selesai,
            ];
        }

        return [null, null, null, null, null];
    }

    /**
     * @return array{0:?float,1:?string}
     */
    private function assessmentSnapshot(Student $student): array
    {
        $student->loadMissing('sidangSubmission.assessments');
        $submission = $student->sidangSubmission;

        if (! $submission) {
            return [null, null];
        }

        $finalScore = $this->assessmentService->finalScore($submission);

        if ($finalScore === null) {
            return [null, null];
        }

        return [$finalScore, $this->assessmentService->letterGrade($finalScore)];
    }
}
