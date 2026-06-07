<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefenseSubmission;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DefenseController extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->student;

        $submission = $student->sidangSubmission()->first();

        return response()->json([
            'submission' => $submission ? [
                'id' => $submission->id,
                'laporan_path' => $submission->laporan_path,
                'poster_path' => $submission->poster_path,
                'foto_kegiatan_1_path' => $submission->foto_kegiatan_1_path,
                'foto_kegiatan_2_path' => $submission->foto_kegiatan_2_path,
                'krs_path' => $submission->krs_path,
                'status' => $submission->status,
                'scheduled_date' => $submission->scheduled_date?->format('Y-m-d'),
                'scheduled_time' => $submission->scheduled_time,
                'room' => $submission->room,
                'dosen_penguji_1' => $submission->dosen_penguji_1,
                'dosen_penguji_2' => $submission->dosen_penguji_2,
                'submitted_at' => $submission->submitted_at,
            ] : null,
            'access_status' => $student->access_status,
        ]);
    }

    public function store(Request $request)
    {
        $student = $request->user()->student;

        if ($student->access_status !== 'LogbookComplete') {
            return response()->json(['message' => '6 logbook harus disetujui terlebih dahulu.'], 403);
        }

        if ($student->sidangSubmission) {
            return response()->json(['message' => 'Sidang sudah pernah diajukan.'], 422);
        }

        $validated = $request->validate([
            'laporan' => 'required|file|mimes:pdf|max:10240',
            'poster' => 'required|file|mimes:pdf|max:5120',
            'foto_kegiatan_1' => 'required|file|mimes:pdf|max:5120',
            'foto_kegiatan_2' => 'required|file|mimes:pdf|max:5120',
            'krs' => 'required|file|mimes:pdf|max:5120',
        ]);

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
                    'krs_path' => $storedPaths['krs'],
                    'status' => 'Pending',
                ]);

                $lockedStudent->update(['access_status' => 'MenungguSidang']);
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
        $lecturer = $request->user()->lecturer;
        if (! $lecturer || ! $lecturer->study_program) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->with(['sidangSubmission', 'dpm:id,lecturer_name'])
            ->select(['id', 'nim', 'name', 'study_program', 'dpm_id', 'access_status'])
            ->get();

        return response()->json(['students' => $students]);
    }

    public function scheduleSidang(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->firstOrFail();

        $submission = $student->sidangSubmission;
        if (! $submission || $submission->status !== 'Pending') {
            return response()->json(['message' => 'Submission tidak valid untuk dijadwalkan.'], 422);
        }

        $request->validate([
            'scheduled_date' => 'required|date|after:today',
            'scheduled_time' => 'nullable|string|max:10',
            'room' => 'nullable|string|max:100',
            'dosen_penguji_1' => 'required|string|max:255',
            'dosen_penguji_2' => 'required|string|max:255',
        ]);

        $submission->update([
            'status' => 'Scheduled',
            'scheduled_date' => $request->scheduled_date,
            'scheduled_time' => $request->scheduled_time,
            'room' => $request->room,
            'dosen_penguji_1' => $request->dosen_penguji_1,
            'dosen_penguji_2' => $request->dosen_penguji_2,
            'scheduled_by' => $lecturer->id,
            'scheduled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Jadwal sidang berhasil ditetapkan.',
        ]);
    }

    public function completeCycle(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->firstOrFail();

        $submission = $student->sidangSubmission;
        if (! $submission || $submission->status !== 'Scheduled') {
            return response()->json(['message' => 'Sidang belum dijadwalkan.'], 422);
        }

        $student->update([
            'access_status' => 'SiklusSelesai',
            'dpm_id' => null,
            'is_independent' => false,
            'form1_data' => null,
            'form1_pdf_path' => null,
            'form1_rejection_reason' => null,
        ]);

        return response()->json([
            'message' => 'Siklus magang berhasil diselesaikan.',
            'access_status' => 'SiklusSelesai',
        ]);
    }
}
