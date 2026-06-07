<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Student;
use App\Services\LogbookReviewService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $logbooks = Logbook::where('student_id', $student->id)
            ->orderByDesc('tanggal')
            ->get();

        return response()->json([
            'logbooks' => $logbooks,
            'approved_logbook_count' => $student->logbooks()
                ->where('status', 'Approved')
                ->count(),
            'internship_period' => $this->internshipPeriod($student),
        ]);
    }

    public function store(Request $request)
    {
        $student = $request->user()->student;

        if ($student->access_status !== 'HasDPM' && $student->access_status !== 'LogbookComplete') {
            return response()->json(['message' => 'DPM harus ditugaskan terlebih dahulu.'], 403);
        }

        $validated = $request->validate([
            'tanggal' => $this->dateRules($student),
            'kegiatan_harian' => 'required|string',
            'hasil' => 'required|string',
        ]);

        $validated['student_id'] = $student->id;

        try {
            $logbook = Logbook::create($validated);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'tanggal' => 'Logbook untuk tanggal ini sudah ada.',
            ]);
        }

        return response()->json(['message' => 'Logbook berhasil disimpan.', 'logbook' => $logbook], 201);
    }

    public function update(Request $request, $id)
    {
        $student = $request->user()->student;

        $logbook = Logbook::where('id', $id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['PendingReview', 'Rejected'])
            ->firstOrFail();

        $validated = $request->validate([
            'tanggal' => $this->dateRules($student, $logbook->id, false),
            'kegiatan_harian' => 'sometimes|string',
            'hasil' => 'sometimes|string',
        ]);

        // Edit logbook berarti DPM perlu review ulang.
        $validated['status'] = 'PendingReview';
        $validated['dpm_note'] = null;

        try {
            $logbook->update($validated);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'tanggal' => 'Logbook untuk tanggal ini sudah ada.',
            ]);
        }

        return response()->json(['message' => 'Logbook berhasil diperbarui.', 'logbook' => $logbook]);
    }

    public function indexForDpm(Request $request)
    {
        $lecturer = $request->user()->lecturer;

        $logbooks = Logbook::whereHas('student', function ($q) use ($lecturer) {
            $q->where('dpm_id', $lecturer->id);
        })
            ->with('student:id,nim,name')
            ->orderByDesc('tanggal')
            ->get();

        return response()->json(['logbooks' => $logbooks]);
    }

    public function approve(Request $request, int $id, LogbookReviewService $reviewService)
    {
        $lecturer = $request->user()->lecturer;

        $student = $reviewService->approve($id, $lecturer->id);

        return response()->json([
            'message' => 'Logbook disetujui.',
            'approved_logbook_count' => $student->approved_logbook_count,
        ]);
    }

    public function reject(Request $request, int $id, LogbookReviewService $reviewService)
    {
        $validated = $request->validate(['note' => 'nullable|string|max:500']);

        $lecturer = $request->user()->lecturer;

        $reviewService->reject($id, $lecturer->id, $validated['note'] ?? null);

        return response()->json(['message' => 'Logbook ditolak.']);
    }

    private function dateRules(Student $student, ?int $ignoreId = null, bool $required = true): array
    {
        $period = $this->internshipPeriod($student);

        if (! $period) {
            throw ValidationException::withMessages([
                'tanggal' => 'Periode magang belum tersedia. Lengkapi pengajuan pembimbing terlebih dahulu.',
            ]);
        }

        return [
            $required ? 'required' : 'sometimes',
            'date',
            'after_or_equal:'.$period['start_date'],
            'before_or_equal:'.$period['maximum_date'],
            function (string $attribute, mixed $value, \Closure $fail) use ($student, $ignoreId): void {
                $duplicateExists = Logbook::query()
                    ->where('student_id', $student->id)
                    ->whereDate('tanggal', $value)
                    ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                    ->exists();

                if ($duplicateExists) {
                    $fail('Logbook untuk tanggal ini sudah ada.');
                }
            },
        ];
    }

    private function internshipPeriod(Student $student): ?array
    {
        $application = $student->supervisorApplication;

        if (! $application?->mulai_magang || ! $application->selesai_magang) {
            return null;
        }

        return [
            'start_date' => $application->mulai_magang->toDateString(),
            'end_date' => $application->selesai_magang->toDateString(),
            'maximum_date' => $application->selesai_magang->min(today())->toDateString(),
        ];
    }
}
