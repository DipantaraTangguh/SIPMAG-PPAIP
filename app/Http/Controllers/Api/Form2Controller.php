<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form2Submission;
use Illuminate\Http\Request;

class Form2Controller extends Controller
{
    /**
     * GET /api/form2
     * List current student's Form 2 submissions.
     */
    public function index(Request $request)
    {
        $student = $request->user()->student;

        $submissions = Form2Submission::where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json(['submissions' => $submissions]);
    }

    /**
     * POST /api/form2
     * Submit a new Form 2 (Mandiri track).
     */
    public function store(Request $request)
    {
        $student = $request->user()->student;

        if (!in_array($student->access_status, ['ApprovedForm1', 'HasApplication'])) {
            return response()->json(['message' => 'Form 1 harus disetujui terlebih dahulu.'], 403);
        }

        $validated = $request->validate([
            'company_name'      => 'required|string|max:255',
            'alamat_perusahaan' => 'required|string',
            'lingkup_magang'    => 'required|string',
            'tanggal_mulai'     => 'required|date',
            'tanggal_selesai'   => 'required|date|after:tanggal_mulai',
        ]);

        $validated['student_id'] = $student->id;

        $submission = Form2Submission::create($validated);

        // Mark Mandiri track. access_status stays at ApprovedForm1 until PPAIP approves.
        if (!$student->is_independent) {
            $student->update(['is_independent' => true]);
        }

        return response()->json([
            'message'    => 'Form 2 berhasil diajukan.',
            'submission' => $submission,
        ], 201);
    }

    /**
     * GET /api/ppaip/form2
     * List all Form 2 submissions for PPAIP review.
     */
    public function indexForPpaip()
    {
        $submissions = Form2Submission::with('student:id,nim,name,study_program')
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json(['submissions' => $submissions]);
    }

    /**
     * POST /api/ppaip/form2/{id}/approve
     */
    public function approve($id)
    {
        $submission = Form2Submission::where('id', $id)
            ->where('status', 'PendingReview')
            ->firstOrFail();

        $submission->update([
            'status'           => 'ApprovedForm2',
            'rejection_reason' => null,
        ]);

        // Promote the student's access_status now that Form 2 is approved.
        $student = $submission->student;
        if ($student && $student->access_status === 'ApprovedForm1') {
            $student->update(['access_status' => 'HasApplication']);
        }

        return response()->json(['message' => 'Form 2 disetujui.', 'submission' => $submission]);
    }

    /**
     * POST /api/ppaip/form2/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $submission = Form2Submission::where('id', $id)
            ->where('status', 'PendingReview')
            ->firstOrFail();

        $submission->update([
            'status'           => 'RejectedForm2',
            'rejection_reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Form 2 ditolak.', 'submission' => $submission]);
    }
}
