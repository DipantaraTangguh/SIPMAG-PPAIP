<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;

class Form1Controller extends Controller
{
    /**
     * GET /api/form1
     * Get current student's Form 1 submission data.
     */
    public function show(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        return response()->json([
            'form1'         => $student->form1_data,
            'access_status' => $student->access_status,
            'pdf_path'      => $student->form1_pdf_path,
            'rejection_reason' => $student->form1_rejection_reason,
        ]);
    }

    /**
     * POST /api/form1
     * Submit Form 1 (mahasiswa only).
     */
    public function store(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        if (!in_array($student->access_status, ['Unverified', 'RejectedForm1'])) {
            return response()->json(['message' => 'Form 1 sudah diajukan atau disetujui.'], 403);
        }

        $validated = $request->validate([
            'semester'     => 'required|string',
            'jumlahSKS'    => 'required|string',
            'ipk'          => 'required|string',
            'skemaMagang'  => 'required|string|in:Mitra,Mandiri,Kewirausahaan',
            'topikMagang'  => 'nullable|string',
            'outputTarget' => 'required|string',
        ]);

        $student->update([
            'form1_data'             => $validated,
            'access_status'          => 'PendingReview',
            'form1_rejection_reason' => null,
        ]);

        return response()->json([
            'message' => 'Form 1 berhasil diajukan.',
            'access_status' => 'PendingReview',
        ], 201);
    }

    /**
     * GET /api/kaprodi/form1
     * List Form 1 submissions for Kaprodi review (scoped by study_program).
     */
    public function indexForKaprodi(Request $request)
    {
        $lecturer = $request->user()->lecturer;
        if (!$lecturer || !$lecturer->study_program) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('study_program', $lecturer->study_program)
            ->whereIn('access_status', ['PendingReview', 'ApprovedForm1', 'RejectedForm1'])
            ->whereNotNull('form1_data')
            ->select(['id', 'nim', 'name', 'study_program', 'access_status', 'form1_data', 'form1_rejection_reason', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['submissions' => $students]);
    }

    /**
     * POST /api/kaprodi/form1/{studentId}/approve
     */
    public function approve(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'PendingReview')
            ->firstOrFail();

        $student->update([
            'access_status'          => 'ApprovedForm1',
            'form1_rejection_reason' => null,
        ]);

        return response()->json([
            'message' => 'Form 1 disetujui.',
            'access_status' => 'ApprovedForm1',
        ]);
    }

    /**
     * POST /api/kaprodi/form1/{studentId}/reject
     */
    public function reject(Request $request, $studentId)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'PendingReview')
            ->firstOrFail();

        $student->update([
            'access_status'          => 'RejectedForm1',
            'form1_rejection_reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Form 1 ditolak.',
            'access_status' => 'RejectedForm1',
        ]);
    }
}
