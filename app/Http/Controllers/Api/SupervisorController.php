<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Services\DpmAssignmentService;
use App\Support\StoredFilePath;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->student;

        $application = SupervisorApplication::where('student_id', $student->id)->first();

        return response()->json([
            'application' => $application,
            'dpm'         => $student->dpm ? [
                'name'    => $student->dpm->lecturer_name,
                'nidn'    => $student->dpm->nidn,
                'contact' => $student->dpm->contact,
            ] : null,
        ]);
    }
    public function store(Request $request)
    {
        $student = $request->user()->student;

        // Request DPM baru masuk kalau tahap perusahaan sudah valid.
        // Jalur mitra: harus sudah punya lamaran aktif.
        // Jalur mandiri: Form 2 approved dihitung setara lamaran.
        if (!in_array($student->access_status, ['HasApplication'])) {
            return response()->json(['message' => 'Anda belum menyelesaikan tahap sebelumnya.'], 403);
        }

        if (SupervisorApplication::where('student_id', $student->id)->exists()) {
            return response()->json(['message' => 'Pengajuan sudah pernah diajukan.'], 422);
        }

        $request->validate([
            'company_name'     => 'required|string|max:255',
            'company_contact'  => 'required|string|max:255',
            'nama_praktisi'    => 'required|string|max:255',
            'jabatan_praktisi' => 'required|string|max:255',
            'no_telepon'       => 'required|string|max:255',
            'email'            => 'required|email|max:255',
            'mulai_magang'     => 'required|date',
            'selesai_magang'   => 'required|date|after_or_equal:mulai_magang',
            'loa_file'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $loaPath = $request->file('loa_file')->store('loa', 'local');

        SupervisorApplication::create([
            'student_id'       => $student->id,
            'company_name'     => $request->company_name,
            'company_contact'  => $request->company_contact,
            'nama_praktisi'    => $request->nama_praktisi,
            'jabatan_praktisi' => $request->jabatan_praktisi,
            'no_telepon'       => $request->no_telepon,
            'email'            => $request->email,
            'mulai_magang'     => $request->mulai_magang,
            'selesai_magang'   => $request->selesai_magang,
            'loa_path'         => $loaPath,
        ]);

        return response()->json(['message' => 'Pengajuan pembimbing berhasil dikirim.'], 201);
    }
    public function indexForKaprodi(Request $request)
    {
        $lecturer = $request->user()->lecturer;

        $applications = SupervisorApplication::whereHas('student', function ($q) use ($lecturer) {
            $q->where('study_program', $lecturer->study_program);
        })
        ->with('student:id,nim,name,study_program,dpm_id')
        ->orderByDesc('submitted_at')
        ->get();

        return response()->json(['applications' => $applications]);
    }
    public function assignDpm(Request $request, DpmAssignmentService $assignmentService)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'lecturer_id' => 'required|integer|exists:lecturers,id',
        ]);

        $kaprodi = $request->user()->lecturer;
        if (! $kaprodi || ! $kaprodi->study_program) {
            return response()->json(['message' => 'Profil Kaprodi tidak valid.'], 403);
        }

        $student = Student::where('id', $validated['student_id'])
            ->where('study_program', $kaprodi->study_program)
            ->firstOrFail();

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

        if (!$application->loa_path) {
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
        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->firstOrFail();

        $application = SupervisorApplication::where('student_id', $student->id)->firstOrFail();

        if (!$application->loa_path) {
            return response()->json(['message' => 'LoA tidak tersedia.'], 404);
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $application->loa_path);
        if (! $path) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file($path);
    }
}
