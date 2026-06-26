<?php

namespace App\Services;

use App\Models\Student;
use App\Support\StoredFilePath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PdfService
{
    public function downloadForm1ApprovalLetter(Student $student): Response
    {
        $student->loadMissing('form1Approver');
        $kaprodi = $student->form1Approver;

        Carbon::setLocale('id');

        $pdf = Pdf::loadView('pdf.surat-keterangan', [
            'student' => $student,
            'form1' => $student->form1_data ?? [],
            'kaprodiName' => $kaprodi->lecturer_name ?? '—',
            'kaprodiNidn' => $kaprodi->nidn ?? '—',
            'signatureSrc' => $this->signatureSource($kaprodi?->signature_path),
            'studentSignatureSrc' => $this->studentSignatureSource($student->form1_data['studentSignaturePath'] ?? null),
            'logoSrc' => $this->logoSource(),
            'submittedDate' => optional($student->updated_at)->translatedFormat('d F Y') ?? '—',
            'approvalDate' => optional($student->form1_approved_at)->translatedFormat('d F Y') ?? '—',
        ])->setPaper('a4');

        return $pdf->download('Surat_Keterangan_Form1_'.$student->nim.'.pdf');
    }

    private function studentSignatureSource(?string $signaturePath): ?string
    {
        if (! $signaturePath) {
            return null;
        }

        $path = StoredFilePath::resolve(storage_path('app/private'), $signaturePath);

        return $path ? $this->imageDataUri($path) : null;
    }

    private function signatureSource(?string $signaturePath): ?string
    {
        if (! $signaturePath) {
            return null;
        }

        $path = StoredFilePath::resolve(storage_path('app/public'), $signaturePath);

        return $path ? $this->imageDataUri($path) : null;
    }

    private function logoSource(): ?string
    {
        $path = public_path('assets/images/logo-ubakrie.png');

        return file_exists($path) ? $this->imageDataUri($path) : null;
    }

    private function imageDataUri(string $path): string
    {
        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
