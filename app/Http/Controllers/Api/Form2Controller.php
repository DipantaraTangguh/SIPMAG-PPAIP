<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Form2\RejectForm2Request;
use App\Http\Requests\Form2\StoreForm2Request;
use App\Http\Resources\Form2SubmissionResource;
use App\Models\Form2Submission;
use App\Models\User;
use App\Services\StudentStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Form2Controller extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Form2Submission::class);

        $student = $this->authenticatedUser($request)->student;

        $submissions = Form2Submission::where('student_id', $student->id)
            ->orderByDesc('submitted_at')
            ->paginate($this->perPage($request));

        return response()->json([
            'submissions' => $this->resourceCollection($request, Form2SubmissionResource::class, $submissions),
        ]);
    }

    public function store(StoreForm2Request $request)
    {
        Gate::authorize('create', Form2Submission::class);

        $student = $this->authenticatedUser($request)->student;

        if (! in_array($student->access_status, ['ApprovedForm1', 'HasApplication'])) {
            return response()->json(['message' => 'Form 1 harus disetujui terlebih dahulu.'], 403);
        }

        $validated = $request->validated();

        $validated['student_id'] = $student->id;

        $submission = Form2Submission::create($validated);

        // Tandai jalur mandiri, tapi akses belum naik sebelum PPAIP approve.
        if (! $student->is_independent) {
            $student->update(['is_independent' => true]);
        }

        return response()->json([
            'message' => 'Form 2 berhasil diajukan.',
            'submission' => Form2SubmissionResource::make($submission)->resolve($request),
        ], 201);
    }

    public function indexForPpaip(Request $request)
    {
        Gate::authorize('viewAny', Form2Submission::class);

        $submissions = Form2Submission::with('student:id,nim,name,study_program')
            ->orderByDesc('submitted_at')
            ->paginate($this->perPage($request));

        return response()->json([
            'submissions' => $this->resourceCollection($request, Form2SubmissionResource::class, $submissions),
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $submission = Form2Submission::where('id', $id)
            ->where('status', 'PendingReview')
            ->firstOrFail();

        Gate::authorize('review', $submission);

        $submission->update([
            'status' => 'ApprovedForm2',
            'rejection_reason' => null,
        ]);

        // Form 2 sudah aman, mahasiswa boleh lanjut ke tahap DPM.
        $student = $submission->student;
        if ($student && $student->access_status === 'ApprovedForm1') {
            app(StudentStateMachine::class)->transition($student, 'HasApplication');
        }

        return response()->json([
            'message' => 'Form 2 disetujui.',
            'submission' => Form2SubmissionResource::make($submission)->resolve($request),
        ]);
    }

    public function reject(RejectForm2Request $request, int $id)
    {
        $validated = $request->validated();

        $submission = Form2Submission::where('id', $id)
            ->where('status', 'PendingReview')
            ->firstOrFail();

        Gate::authorize('review', $submission);

        $submission->update([
            'status' => 'RejectedForm2',
            'rejection_reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Form 2 ditolak.',
            'submission' => Form2SubmissionResource::make($submission)->resolve($request),
        ]);
    }

    private function authenticatedUser(Request $request): User
    {
        $user = ($request->getUserResolver())();

        abort_unless($user instanceof User, 401);

        return $user;
    }
}
