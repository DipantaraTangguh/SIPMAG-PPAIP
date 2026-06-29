<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Form1\RejectForm1Request;
use App\Http\Requests\Form1\StoreForm1Request;
use App\Http\Resources\Form1Resource;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Services\PdfService;
use App\Services\StudentStateMachine;
use App\Support\StoredFilePath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class Form1Controller extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        Gate::authorize('view', $student);

        // Sekalian bawa data approver buat tampilan status.
        $student->load('form1Approver');

        return response()->json(Form1Resource::make($student)->resolve($request));
    }

    public function store(StoreForm1Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        if (! in_array($student->access_status, ['Unverified', 'RejectedForm1'])) {
            return response()->json(['message' => 'Form 1 sudah diajukan atau disetujui.'], 403);
        }

        if ($student->semester === null || $student->jumlah_sks === null || $student->ipk === null) {
            return response()->json([
                'message' => 'Data akademik mahasiswa belum lengkap. Hubungi admin akademik sebelum mengajukan Form 1.',
            ], 422);
        }

        $validated = $request->validated();

        $transkripPath = $request->file('transkrip')->store('transkrip', 'local');
        $studentSignaturePath = $request->file('studentSignature')->store('student-signatures', 'local');

        unset($validated['transkrip']);
        unset($validated['studentSignature']);

        // Academic values are authoritative server-side data, never request input.
        $form1Data = [
            'semester' => $student->semester,
            'jumlahSKS' => $student->jumlah_sks,
            'ipk' => $student->ipk,
            'skemaMagang' => $validated['skemaMagang'],
            'topikMagang' => $validated['topikMagang'],
            'outputTarget' => $validated['outputTarget'],
            'studentSignaturePath' => $studentSignaturePath,
        ];

        $student->fill([
            'form1_data' => $form1Data,
            'form1_pdf_path' => $transkripPath,
            'form1_rejection_reason' => null,
        ]);

        app(StudentStateMachine::class)->transition($student, 'PendingReview');

        return response()->json([
            'message' => 'Form 1 berhasil diajukan.',
            'access_status' => 'PendingReview',
        ], 201);
    }

    public function indexForKaprodi(Request $request)
    {
        Gate::authorize('viewAny', Student::class);

        $lecturer = $request->user()->lecturer;
        if (! $lecturer || ! $lecturer->study_program) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $students = Student::where('study_program', $lecturer->study_program)
            ->whereIn('access_status', ['PendingReview', 'ApprovedForm1', 'RejectedForm1'])
            ->whereNotNull('form1_data')
            ->select(['id', 'nim', 'name', 'study_program', 'access_status', 'form1_data', 'form1_pdf_path', 'form1_rejection_reason', 'updated_at'])
            ->orderByDesc('updated_at')
            ->paginate($this->perPage($request));

        return response()->json([
            'submissions' => $this->resourceCollection($request, StudentResource::class, $students),
        ]);
    }

    public function approve(Request $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;

        $student = Student::where('id', $studentId)
            ->where('access_status', 'PendingReview')
            ->firstOrFail();

        Gate::authorize('reviewForm1', $student);

        // PDF butuh tanda tangan Kaprodi, jadi tahan dulu kalau belum ada.
        if (! $lecturer->signature_path) {
            return response()->json([
                'message' => 'Anda harus mengunggah tanda tangan digital terlebih dahulu melalui menu "Profil Saya" sebelum dapat menyetujui Form 1.',
            ], 422);
        }

        app(StudentStateMachine::class)->transition($student, 'ApprovedForm1', [
            'form1_rejection_reason' => null,
            'form1_approved_by' => $lecturer->id,
            'form1_approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Form 1 disetujui.',
            'access_status' => 'ApprovedForm1',
        ]);
    }

    public function reject(RejectForm1Request $request, int $studentId)
    {
        $validated = $request->validated();

        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $studentId)
            ->where('access_status', 'PendingReview')
            ->firstOrFail();

        Gate::authorize('reviewForm1', $student);

        app(StudentStateMachine::class)->transition($student, 'RejectedForm1', [
            'form1_rejection_reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Form 1 ditolak.',
            'access_status' => 'RejectedForm1',
        ]);
    }

    public function downloadSuratKeterangan(Request $request, PdfService $pdfService)
    {
        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        Gate::authorize('view', $student);

        if (! $student->form1_approved_at || ! $student->form1_data) {
            return response()->json(['message' => 'Form 1 belum disetujui.'], 403);
        }

        return $pdfService->downloadForm1ApprovalLetter($student);
    }

    public function downloadTranskrip(Request $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;
        $student = Student::findOrFail($studentId);

        Gate::authorize('viewTranscript', $student);

        if (! $student->form1_pdf_path) {
            return response()->json(['message' => 'Transkrip tidak tersedia.'], 404);
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $student->form1_pdf_path);
        if (! $path) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file($path);
    }
}
