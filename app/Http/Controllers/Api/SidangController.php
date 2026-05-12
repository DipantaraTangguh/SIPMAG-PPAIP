<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SidangSubmission;
use App\Models\Student;
use Illuminate\Http\Request;

class SidangController extends Controller
{
    /**
     * GET /api/sidang
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
     * POST /api/sidang
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

        SidangSubmission::create([
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
     * GET /api/ppaip/sidang
     * List students waiting for sidang (PPAIP only).
     */
    public function indexForPpaip()
    {
        $students = Student::where('access_status', 'MenungguSidang')
            ->with(['sidangSubmission', 'dpm:id,lecturer_name'])
            ->select(['id', 'nim', 'name', 'study_program', 'dpm_id', 'access_status'])
            ->get();

        return response()->json(['students' => $students]);
    }

    /**
     * POST /api/ppaip/sidang/{studentId}/complete
     * Cycle reset — PPAIP confirms offline sidang is done.
     */
    public function completeCycle($studentId)
    {
        $student = Student::where('id', $studentId)
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
