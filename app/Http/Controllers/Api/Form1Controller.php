<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        // Load the approver relationship
        $student->load('form1Approver');

        $approver = null;
        if ($student->form1Approver) {
            $approver = [
                'name'         => $student->form1Approver->lecturer_name,
                'nidn'         => $student->form1Approver->nidn,
                'role'         => 'Kaprodi ' . $student->form1Approver->study_program,
                'approvalDate' => $student->form1_approved_at?->format('d/m/Y'),
            ];
        }

        return response()->json([
            'form1'            => $student->form1_data,
            'access_status'    => $student->access_status,
            'pdf_path'         => $student->form1_pdf_path,
            'rejection_reason' => $student->form1_rejection_reason,
            'approver'         => $approver,
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
            'transkrip'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Store transcript file if provided
        $transkripPath = null;
        if ($request->hasFile('transkrip')) {
            $transkripPath = $request->file('transkrip')->store('transkrip', 'local');
        }

        // Remove transkrip from validated data before storing as JSON
        unset($validated['transkrip']);

        $student->update([
            'form1_data'             => $validated,
            'form1_pdf_path'         => $transkripPath,
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
            ->select(['id', 'nim', 'name', 'study_program', 'access_status', 'form1_data', 'form1_pdf_path', 'form1_rejection_reason', 'updated_at'])
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

        // Guard: Kaprodi must have uploaded their digital signature
        if (!$lecturer->signature_path) {
            return response()->json([
                'message' => 'Anda harus mengunggah tanda tangan digital terlebih dahulu melalui menu "Profil Saya" sebelum dapat menyetujui Form 1.',
            ], 422);
        }

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'PendingReview')
            ->firstOrFail();

        $student->update([
            'access_status'          => 'ApprovedForm1',
            'form1_rejection_reason' => null,
            'form1_approved_by'      => $lecturer->id,
            'form1_approved_at'      => now(),
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

    /**
     * GET /api/form1/surat-keterangan
     * Generate and stream the Form 1 "Surat Keterangan Memenuhi Syarat Akademik" PDF.
     * Only available after Kaprodi has approved Form 1.
     */
    public function downloadSuratKeterangan(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        if ($student->access_status !== 'ApprovedForm1') {
            return response()->json(['message' => 'Form 1 belum disetujui.'], 403);
        }

        $student->load('form1Approver');
        $kaprodi = $student->form1Approver;

        $signatureSrc = null;
        if ($kaprodi && $kaprodi->signature_path) {
            $absPath = storage_path('app/public/' . $kaprodi->signature_path);
            if (file_exists($absPath)) {
                $mime = mime_content_type($absPath) ?: 'image/png';
                $signatureSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absPath));
            }
        }

        $logoPath = public_path('assets/images/logo-ubakrie.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        Carbon::setLocale('id');
        $submittedDate = optional($student->updated_at)->translatedFormat('d F Y') ?? '—';
        $approvalDate  = optional($student->form1_approved_at)->translatedFormat('d F Y') ?? '—';

        $pdf = Pdf::loadView('pdf.surat-keterangan', [
            'student'       => $student,
            'form1'         => $student->form1_data ?? [],
            'kaprodiName'   => $kaprodi->lecturer_name ?? '—',
            'kaprodiNidn'   => $kaprodi->nidn ?? '—',
            'signatureSrc'  => $signatureSrc,
            'logoSrc'       => $logoSrc,
            'submittedDate' => $submittedDate,
            'approvalDate'  => $approvalDate,
        ])->setPaper('a4');

        $filename = 'Surat_Keterangan_Form1_' . $student->nim . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * GET /api/kaprodi/students/{studentId}/transkrip
     * Download/view student transcript — Kaprodi only, scoped to own prodi.
     */
    public function downloadTranskrip(Request $request, $studentId)
    {
        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->firstOrFail();

        if (!$student->form1_pdf_path) {
            return response()->json(['message' => 'Transkrip tidak tersedia.'], 404);
        }

        $path = storage_path('app/private/' . $student->form1_pdf_path);

        if (!file_exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file($path);
    }
}
