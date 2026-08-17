<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reset siklus magang mandiri oleh mahasiswa: mengarsipkan child record siklus
 * berjalan (soft-delete) dan mengembalikan mahasiswa ke Unverified agar bisa
 * mendaftar magang berikutnya. Riwayat di internship_cycles TIDAK disentuh.
 */
class InternshipCycleResetService
{
    private const RESETTABLE_STATUSES = ['CycleCompleted', 'ElectiveCompleted'];

    public function __construct(private readonly StudentStateMachine $stateMachine) {}

    public function canReset(Student $student): bool
    {
        return in_array($student->access_status, self::RESETTABLE_STATUSES, true);
    }

    public function reset(Student $student): void
    {
        DB::transaction(function () use ($student): void {
            $locked = Student::query()->lockForUpdate()->findOrFail($student->id);

            if (! $this->canReset($locked)) {
                throw ValidationException::withMessages([
                    'cycle' => 'Siklus hanya bisa direset setelah magang selesai.',
                ]);
            }

            // Arsipkan (soft-delete) semua child record siklus berjalan.
            $locked->form2Submissions()->delete();
            $locked->logbooks()->delete();
            $locked->applications()->delete();
            $locked->sidangSubmission()->delete();
            $locked->supervisorApplication()->delete();

            // Bersihkan state siklus di row students lalu kembali ke Unverified.
            $this->stateMachine->transition($locked, 'Unverified', [
                'form1_data'             => null,
                'form1_pdf_path'         => null,
                'form1_approved_by'      => null,
                'form1_approved_at'      => null,
                'form1_rejection_reason' => null,
                'dpm_id'                 => null,
                'is_independent'         => false,
            ]);
        });
    }
}
