<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logbooks\RejectLogbookRequest;
use App\Http\Requests\Logbooks\StoreLogbookRequest;
use App\Http\Requests\Logbooks\UpdateLogbookRequest;
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
            ->paginate($this->perPage($request));

        return response()->json([
            'logbooks' => $logbooks,
            'approved_logbook_count' => $student->logbooks()
                ->where('status', 'Approved')
                ->count(),
            'internship_period' => $this->internshipPeriod($student),
        ]);
    }

    public function store(StoreLogbookRequest $request)
    {
        $student = $request->user()->student;

        if ($student->access_status !== 'HasDPM' && $student->access_status !== 'LogbookComplete') {
            return response()->json(['message' => 'DPM harus ditugaskan terlebih dahulu.'], 403);
        }

        $validated = $request->validated();

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

    public function update(UpdateLogbookRequest $request, int $id)
    {
        $student = $request->user()->student;

        $logbook = Logbook::where('id', $id)
            ->where('student_id', $student->id)
            ->whereIn('status', ['PendingReview', 'Rejected'])
            ->firstOrFail();

        $validated = $request->validated();

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
            ->paginate($this->perPage($request));

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

    public function reject(RejectLogbookRequest $request, int $id, LogbookReviewService $reviewService)
    {
        $validated = $request->validated();

        $lecturer = $request->user()->lecturer;

        $reviewService->reject($id, $lecturer->id, $validated['note'] ?? null);

        return response()->json(['message' => 'Logbook ditolak.']);
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
