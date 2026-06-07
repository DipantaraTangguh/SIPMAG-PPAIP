<?php

namespace App\Services;

use App\Models\Logbook;
use App\Models\Student;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LogbookReviewService
{
    public function approve(int $logbookId, int $lecturerId): Student
    {
        return DB::transaction(function () use ($logbookId, $lecturerId): Student {
            $logbook = $this->lockPendingLogbook($logbookId, $lecturerId);
            $student = Student::query()->lockForUpdate()->findOrFail($logbook->student_id);

            $logbook->update([
                'status' => 'Approved',
                'dpm_note' => null,
            ]);

            $approvedCount = $student->logbooks()
                ->where('status', 'Approved')
                ->count();

            if ($approvedCount >= 6 && $student->access_status === 'HasDPM') {
                $student->access_status = 'LogbookComplete';
                $student->save();
            }

            return $student->refresh()->setAttribute('approved_logbook_count', $approvedCount);
        });
    }

    public function reject(int $logbookId, int $lecturerId, ?string $note): Logbook
    {
        return DB::transaction(function () use ($logbookId, $lecturerId, $note): Logbook {
            $logbook = $this->lockPendingLogbook($logbookId, $lecturerId);

            $logbook->update([
                'status' => 'Rejected',
                'dpm_note' => $note,
            ]);

            return $logbook->refresh();
        });
    }

    private function lockPendingLogbook(int $logbookId, int $lecturerId): Logbook
    {
        $logbook = Logbook::query()
            ->whereKey($logbookId)
            ->whereHas('student', fn ($query) => $query->where('dpm_id', $lecturerId))
            ->lockForUpdate()
            ->first();

        if (! $logbook) {
            throw (new ModelNotFoundException)->setModel(Logbook::class, [$logbookId]);
        }

        if ($logbook->status !== 'PendingReview') {
            throw ValidationException::withMessages([
                'logbook' => 'Logbook ini sudah pernah direview.',
            ]);
        }

        return $logbook;
    }
}
