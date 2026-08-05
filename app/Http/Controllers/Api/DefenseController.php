<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Defense\ScheduleDefenseRequest;
use App\Http\Requests\Defense\StoreDefenseSubmissionRequest;
use App\Http\Resources\DefenseSubmissionResource;
use App\Http\Resources\StudentResource;
use App\Models\DefenseSubmission;
use App\Models\Student;
use App\Services\StudentStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DefenseController extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->student;

        $submission = $student->sidangSubmission()
            ->with(['examinerOne', 'examinerTwo'])
            ->first();

        return response()->json([
            'submission' => $submission
                ? DefenseSubmissionResource::make($submission)->resolve($request)
                : null,
            'access_status' => $student->access_status,
        ]);
    }

    public function store(StoreDefenseSubmissionRequest $request)
    {
        $student = $request->user()->student;

        if ($student->access_status !== 'LogbookComplete') {
            return response()->json(['message' => '6 logbook harus disetujui terlebih dahulu.'], 403);
        }

        if ($student->sidangSubmission) {
            return response()->json(['message' => 'Sidang sudah pernah diajukan.'], 422);
        }

        $validated = $request->validated();

        $storedPaths = [];

        try {
            DB::transaction(function () use ($student, $validated, &$storedPaths): void {
                $lockedStudent = Student::query()->lockForUpdate()->findOrFail($student->id);

                if ($lockedStudent->access_status !== 'LogbookComplete') {
                    abort(403, '6 logbook harus disetujui terlebih dahulu.');
                }

                if ($lockedStudent->sidangSubmission()->exists()) {
                    abort(422, 'Sidang sudah pernah diajukan.');
                }

                foreach (array_keys($validated) as $field) {
                    $storedPaths[$field] = $validated[$field]->store('sidang', 'local');
                }

                DefenseSubmission::create([
                    'student_id' => $lockedStudent->id,
                    'laporan_path' => $storedPaths['laporan'],
                    'poster_path' => $storedPaths['poster'],
                    'foto_kegiatan_1_path' => $storedPaths['foto_kegiatan_1'],
                    'foto_kegiatan_2_path' => $storedPaths['foto_kegiatan_2'],
                    'status' => 'Pending',
                ]);

                app(StudentStateMachine::class)->transition($lockedStudent, 'MenungguSidang');
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete(array_values($storedPaths));

            throw $exception;
        }

        return response()->json([
            'message' => 'Dokumen sidang berhasil dikirim.',
            'access_status' => 'MenungguSidang',
        ], 201);
    }

    public function indexForKaprodi(Request $request)
    {
        Gate::authorize('viewAny', Student::class);

        $lecturer = $request->user()->lecturer;
        if (! $lecturer || ! $lecturer->study_program) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->with(['sidangSubmission.examinerOne', 'sidangSubmission.examinerTwo', 'dpm:id,lecturer_name'])
            ->select(['id', 'nim', 'name', 'study_program', 'dpm_id', 'access_status'])
            ->paginate($this->perPage($request));

        return response()->json([
            'students' => $this->resourceCollection($request, StudentResource::class, $students),
        ]);
    }

    public function scheduleSidang(ScheduleDefenseRequest $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('access_status', 'MenungguSidang')
            ->firstOrFail();

        Gate::authorize('manageDefense', $student);

        $submission = $student->sidangSubmission;
        if (! $submission || $submission->status !== 'Pending') {
            return response()->json(['message' => 'Submission tidak valid untuk dijadwalkan.'], 422);
        }

        Gate::authorize('schedule', $submission);

        $validated = $request->validated();

        $submission->update([
            'status' => 'Scheduled',
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'] ?? null,
            'room' => $validated['room'] ?? null,
            'dosen_penguji_1_id' => $validated['dosen_penguji_1_id'],
            'dosen_penguji_2_id' => $validated['dosen_penguji_2_id'],
            'scheduled_by' => $lecturer->id,
            'scheduled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Jadwal sidang berhasil ditetapkan.',
        ]);
    }

}
