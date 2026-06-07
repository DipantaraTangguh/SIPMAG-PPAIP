<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Form2Submission;
use App\Models\User;
use App\Services\StudentStateMachine;
use Illuminate\Http\Request;

class Form2Controller extends Controller
{
    public function index(Request $request)
    {
        $student = $this->authenticatedUser($request)->student;

        $submissions = Form2Submission::where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json(['submissions' => $submissions]);
    }

    public function store(Request $request)
    {
        $student = $this->authenticatedUser($request)->student;

        if (! in_array($student->access_status, ['ApprovedForm1', 'HasApplication'])) {
            return response()->json(['message' => 'Form 1 harus disetujui terlebih dahulu.'], 403);
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'alamat_perusahaan' => 'required|string',
            'lingkup_magang' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        $validated['student_id'] = $student->id;

        $submission = Form2Submission::create($validated);

        // Tandai jalur mandiri, tapi akses belum naik sebelum PPAIP approve.
        if (! $student->is_independent) {
            $student->update(['is_independent' => true]);
        }

        return response()->json([
            'message' => 'Form 2 berhasil diajukan.',
            'submission' => $submission,
        ], 201);
    }

    public function indexForPpaip()
    {
        $submissions = Form2Submission::with('student:id,nim,name,study_program')
            ->orderByDesc('submitted_at')
            ->get();

        return response()->json(['submissions' => $submissions]);
    }

    public function approve(int $id)
    {
        $submission = Form2Submission::where('id', $id)
            ->where('status', 'PendingReview')
            ->firstOrFail();

        $submission->update([
            'status' => 'ApprovedForm2',
            'rejection_reason' => null,
        ]);

        // Form 2 sudah aman, mahasiswa boleh lanjut ke tahap DPM.
        $student = $submission->student;
        if ($student && $student->access_status === 'ApprovedForm1') {
            app(StudentStateMachine::class)->transition($student, 'HasApplication');
        }

        return response()->json(['message' => 'Form 2 disetujui.', 'submission' => $submission]);
    }

    public function reject(Request $request, int $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $submission = Form2Submission::where('id', $id)
            ->where('status', 'PendingReview')
            ->firstOrFail();

        $submission->update([
            'status' => 'RejectedForm2',
            'rejection_reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Form 2 ditolak.', 'submission' => $submission]);
    }

    private function authenticatedUser(Request $request): User
    {
        $user = ($request->getUserResolver())();

        abort_unless($user instanceof User, 401);

        return $user;
    }
}
