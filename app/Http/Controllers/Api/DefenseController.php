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
     * Get current student's sidang submission.
     */
    public function show(Request $request)
    {
        $student = $request->user()->student;

        return response()->json([
            'submission'    => $student->sidangSubmission,
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
     * Set sidang schedule — Kaprodi only, scoped to own prodi.
     */
    public function setSchedule(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->firstOrFail();

        $request->validate([
            'scheduled_date' => 'required|date|after:today',
            'room'           => 'nullable|string|max:100',
        ]);

        $student->sidangSubmission?->update([
            'scheduled_date' => $request->scheduled_date,
            'room'           => $request->room,
        ]);

        return response()->json([
            'message' => 'Jadwal sidang berhasil ditetapkan.',
        ]);
    }

    /**
     * POST /api/kaprodi/defense/{studentId}/complete
     * Complete the internship cycle — Kaprodi only, scoped to own prodi.
     */
    public function completeCycle(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'MenungguSidang')
            ->firstOrFail();

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
