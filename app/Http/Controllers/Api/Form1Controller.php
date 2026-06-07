<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\StoredFilePath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Form1Controller extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->student;
        if (!$student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        // Sekalian bawa data approver buat tampilan status.
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
            'submitted_at'     => $student->updated_at?->toIso8601String(),
        ]);
    }
    public function store(Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            return response()->json(['message' => 'Profil mahasiswa tidak ditemukan.'], 404);
        }

        if (! in_array($student->access_status, ['Unverified', 'RejectedForm1'])) {
            return response()->json(['message' => 'Form 1 sudah diajukan atau disetujui.'], 403);
        }

        if (! $student->semester || ! $student->jumlah_sks || ! $student->ipk) {
            return response()->json([
                'message' => 'Data akademik mahasiswa belum lengkap. Hubungi admin akademik sebelum mengajukan Form 1.',
            ], 422);
        }

        $validated = $request->validate([
            'skemaMagang' => 'required|string|in:Mitra,Mandiri,Kewirausahaan',
            'topikMagang' => 'required|string|max:2000',
            'outputTarget' => 'required|string|in:Produk,Prototype,Laporan',
            'transkrip' => 'required|file|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png|max:5120',
        ]);

        $transkripPath = $request->file('transkrip')->store('transkrip', 'local');

        unset($validated['transkrip']);

        // Academic values are authoritative server-side data, never request input.
        $form1Data = [
            'semester' => $student->semester,
            'jumlahSKS' => $student->jumlah_sks,
            'ipk' => $student->ipk,
            'skemaMagang' => $validated['skemaMagang'],
            'topikMagang' => $validated['topikMagang'],
            'outputTarget' => $validated['outputTarget'],
        ];

        $student->fill([
            'form1_data' => $form1Data,
            'form1_pdf_path' => $transkripPath,
            'form1_rejection_reason' => null,
        ]);
        $student->access_status = 'PendingReview';
        $student->save();

        return response()->json([
            'message' => 'Form 1 berhasil diajukan.',
            'access_status' => 'PendingReview',
        ], 201);
    }
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
    public function approve(Request $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;

        // PDF butuh tanda tangan Kaprodi, jadi tahan dulu kalau belum ada.
        if (!$lecturer->signature_path) {
            return response()->json([
                'message' => 'Anda harus mengunggah tanda tangan digital terlebih dahulu melalui menu "Profil Saya" sebelum dapat menyetujui Form 1.',
            ], 422);
        }

        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'PendingReview')
            ->firstOrFail();

        $student->fill([
            'form1_rejection_reason' => null,
        ]);
        $student->access_status = 'ApprovedForm1';
        $student->form1_approved_by = $lecturer->id;
        $student->form1_approved_at = now();
        $student->save();

        return response()->json([
            'message' => 'Form 1 disetujui.',
            'access_status' => 'ApprovedForm1',
        ]);
    }
    public function reject(Request $request, int $studentId)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->where('access_status', 'PendingReview')
            ->firstOrFail();

        $student->fill([
            'form1_rejection_reason' => $request->reason,
        ]);
        $student->access_status = 'RejectedForm1';
        $student->save();

        return response()->json([
            'message' => 'Form 1 ditolak.',
            'access_status' => 'RejectedForm1',
        ]);
    }
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
            $absPath = StoredFilePath::resolve(storage_path('app/public'), $kaprodi->signature_path);
            if ($absPath) {
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
    public function downloadTranskrip(Request $request, int $studentId)
    {
        $lecturer = $request->user()->lecturer;
        $student = Student::where('id', $studentId)
            ->where('study_program', $lecturer->study_program)
            ->firstOrFail();

        if (!$student->form1_pdf_path) {
            return response()->json(['message' => 'Transkrip tidak tersedia.'], 404);
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $student->form1_pdf_path);
        if (! $path) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        return response()->file($path);
    }
}
