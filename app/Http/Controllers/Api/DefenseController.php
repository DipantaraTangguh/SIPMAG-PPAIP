<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefenseSubmission;
use App\Models\Student;
use Illuminate\Http\Request;

class DefenseController extends Controller
{
    /**
     * GET /api/defense
     * Get current student's sidang submission (including schedule info).
     */
    public function show(Request $request)
    {
        $student = $request->user()->student;

        $submission = $student->sidangSubmission;

        return response()->json([
            'submission'    => $submission ? [
                'id'              => $submission->id,
                'laporan_path'    => $submission->laporan_path,
                'poster_path'     => $submission->poster_path,
                'krs_path'        => $submission->krs_path,
                'status'          => $submission->status,
                'scheduled_date'  => $submission->scheduled_date?->format('Y-m-d'),
                'scheduled_time'  => $submission->scheduled_time,
                'room'            => $submission->room,
                'dosen_penguji_1' => $submission->dosen_penguji_1,
                'dosen_penguji_2' => $submission->dosen_penguji_2,
                'submitted_at'    => $submission->submitted_at,
            ] : null,
            'access_status' => $student->access_status,
        ]);
    }

    /**
     * POST /api/defense
     * Submit sidang documents (3 PDFs). Requires LogbookComplete.
     */
    public function store(Request $request)
    {
        $student = $request->user()->student;

        if ($student->access_status !== 'LogbookComplete') {
            return response()->json(['message' => '6 logbook harus disetujui terlebih dahulu.'], 403);
        }

        if ($student->sidangSubmission) {
            return response()->json(['message' => 'Sidang sudah pernah diajukan.'], 422);
        }

        $request->validate([
            'laporan' => 'required|file|mimes:pdf|max:2048',
            'poster'  => 'required|file|mimes:pdf|max:2048',
            'krs'     => 'required|file|mimes:pdf|max:2048',
        ]);

        $laporanPath = $request->file('laporan')->store('sidang', 'local');
        $posterPath  = $request->file('poster')->store('sidang', 'local');
        $krsPath     = $request->file('krs')->store('sidang', 'local');

        DefenseSubmission::create([
            'student_id'   => $student->id,
            'laporan_path' => $laporanPath,
            'poster_path'  => $posterPath,
            'krs_path'     => $krsPath,
            'status'       => 'Pending',
        ]);

        $student->update(['access_status' => 'MenungguSidang']);

        return response()->json([
            'message'       => 'Dokumen sidang berhasil dikirim.',
            'access_status' => 'MenungguSidang',
        ], 201);
    }

    /**
     * GET /api/kaprodi/defense
     * List students waiting for sidang — scoped to Kaprodi's prodi.
     */
    public function indexForKaprodi(Request $request)
    {
        $lecturer = $request->user()->lecturer;
        if (!$lecturer || !$lecturer->study_program) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->with(['sidangSubmission', 'dpm:id,lecturer_name'])
            ->select(['id', 'nim', 'name', 'study_program', 'dpm_id', 'access_status'])
            ->get();

        return response()->json(['students' => $students]);
    }

    /**
     * POST /api/kaprodi/defense/{studentId}/schedule
     * Schedule the sidang — Kaprodi sets date, time, room, and 2 examiners.
     * Scoped to own prodi. Only when submission status is Pending.
     */
    public function scheduleSidang(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->firstOrFail();

        $submission = $student->sidangSubmission;
        if (!$submission || $submission->status !== 'Pending') {
            return response()->json(['message' => 'Submission tidak valid untuk dijadwalkan.'], 422);
        }

        $request->validate([
            'scheduled_date'  => 'required|date|after:today',
            'scheduled_time'  => 'nullable|string|max:10',
            'room'            => 'nullable|string|max:100',
            'dosen_penguji_1' => 'required|string|max:255',
            'dosen_penguji_2' => 'required|string|max:255',
        ]);

        $submission->update([
            'status'          => 'Scheduled',
            'scheduled_date'  => $request->scheduled_date,
            'scheduled_time'  => $request->scheduled_time,
            'room'            => $request->room,
            'dosen_penguji_1' => $request->dosen_penguji_1,
            'dosen_penguji_2' => $request->dosen_penguji_2,
            'scheduled_by'    => $lecturer->id,
            'scheduled_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Jadwal sidang berhasil ditetapkan.',
        ]);
    }

    /**
     * POST /api/kaprodi/defense/{studentId}/complete
     * Complete the internship cycle — only when sidang has been scheduled.
     * Kaprodi only, scoped to own prodi.
     */
    public function completeCycle(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->firstOrFail();

        $submission = $student->sidangSubmission;
        if (!$submission || $submission->status !== 'Scheduled') {
            return response()->json(['message' => 'Sidang belum dijadwalkan.'], 422);
        }

        $student->update([
            'access_status'          => 'SiklusSelesai',
            'dpm_id'                 => null,
            'is_independent'         => false,
            'approved_logbook_count' => 0,
            'form1_data'             => null,
            'form1_pdf_path'         => null,
            'form1_rejection_reason' => null,
        ]);

        return response()->json([
            'message'       => 'Siklus magang berhasil diselesaikan.',
            'access_status' => 'SiklusSelesai',
        ]);
    }
}
