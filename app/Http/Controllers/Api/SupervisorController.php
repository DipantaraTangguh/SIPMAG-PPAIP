<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supervisors\AssignDpmRequest;
use App\Http\Requests\Supervisors\StoreSupervisorApplicationRequest;
use App\Http\Resources\LecturerResource;
use App\Http\Resources\SupervisorApplicationResource;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Services\DpmAssignmentService;
use App\Support\StoredFilePath;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupervisorController extends Controller
{
    public function show(Request $request)
    {
        Gate::authorize('viewAny', SupervisorApplication::class);

        $student = $request->user()->student;

        $application = SupervisorApplication::where('student_id', $student->id)->first();
        if ($application) {
            Gate::authorize('view', $application);
        }

        return response()->json([
            'application' => $application
                ? SupervisorApplicationResource::make($application)->resolve($request)
                : null,
            'dpm' => $student->dpm
                ? LecturerResource::make($student->dpm)->resolve($request)
                : null,
        ]);
    }

    public function store(StoreSupervisorApplicationRequest $request)
    {
        Gate::authorize('create', SupervisorApplication::class);

        $student = $request->user()->student;

        // Request DPM baru masuk kalau tahap perusahaan sudah valid.
        // Jalur mitra: harus sudah punya lamaran aktif.
        // Jalur mandiri: Form 2 approved dihitung setara lamaran.
        if (! in_array($student->access_status, ['HasApplication'])) {
            return response()->json(['message' => 'Anda belum menyelesaikan tahap sebelumnya.'], 403);
        }

        if (SupervisorApplication::where('student_id', $student->id)->exists()) {
            return response()->json(['message' => 'Pengajuan sudah pernah diajukan.'], 422);
        }

        $validated = $request->validated();

        $loaPath = $request->file('loa_file')->store('loa', 'local');

        try {
            SupervisorApplication::create([
                'student_id' => $student->id,
                'company_name' => $validated['company_name'],
                'company_contact' => $validated['company_contact'],
                'nama_praktisi' => $validated['nama_praktisi'],
                'jabatan_praktisi' => $validated['jabatan_praktisi'],
                'no_telepon' => $validated['no_telepon'],
                'email' => $validated['email'],
                'mulai_magang' => $validated['mulai_magang'],
                'selesai_magang' => $validated['selesai_magang'],
                'loa_path' => $loaPath,
            ]);
        } catch (UniqueConstraintViolationException) {
            Storage::disk('local')->delete($loaPath);

            throw ValidationException::withMessages([
                'student_id' => 'Pengajuan sudah pernah diajukan.',
            ]);
        }

        return response()->json(['message' => 'Pengajuan pembimbing berhasil dikirim.'], 201);
    }

    public function indexForKaprodi(Request $request)
    {
        Gate::authorize('viewAny', SupervisorApplication::class);

        $lecturer = $request->user()->lecturer;

        $applications = SupervisorApplication::whereHas('student', function ($q) use ($lecturer) {
            $q->where('study_program', $lecturer->study_program);
        })
            ->with('student:id,nim,name,study_program,dpm_id')
            ->orderByDesc('submitted_at')
            ->paginate($this->perPage($request));

        return response()->json([
            'applications' => $this->resourceCollection($request, SupervisorApplicationResource::class, $applications),
        ]);
    }

    public function assignDpm(AssignDpmRequest $request, DpmAssignmentService $assignmentService)
    {
        $validated = $request->validated();

        $kaprodi = $request->user()->lecturer;
        if (! $kaprodi || ! $kaprodi->study_program) {
            return response()->json(['message' => 'Profil Kaprodi tidak valid.'], 403);
        }

        $student = Student::findOrFail($validated['student_id']);

        Gate::authorize('assignDpm', $student);

        $assignmentService->assign($student, (int) $validated['lecturer_id']);

        return response()->json([
            'message' => 'DPM berhasil ditugaskan.',
            'access_status' => 'HasDPM',
        ]);
    }

    public function downloadLoa(Request $request)
    {
        $student = $request->user()->student;
        $application = SupervisorApplication::where('student_id', $student->id)->firstOrFail();

        Gate::authorize('view', $application);

        if (! $application->loa_path) {
            return response()->json(['message' => 'LoA tidak tersedia.'], 404);
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $application->loa_path);
        if (! $path) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file($path);
    }

    public function downloadLoaForKaprodi(Request $request, int $studentId)
    {
        $student = Student::findOrFail($studentId);

        $application = SupervisorApplication::where('student_id', $student->id)->firstOrFail();

        Gate::authorize('view', $application);

        if (! $application->loa_path) {
            return response()->json(['message' => 'LoA tidak tersedia.'], 404);
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $application->loa_path);
        if (! $path) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file($path);
    }
}
