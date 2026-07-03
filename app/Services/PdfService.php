<?php

namespace App\Services;

use App\Models\Form2Submission;
use App\Models\Student;
use App\Support\StoredFilePath;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use ZipArchive;

class PdfService
{
    private const ROMAN_MONTHS = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    public function downloadForm2RequestLetter(Form2Submission $submission): Response
    {
        $submission->loadMissing('student');
        $student = $submission->student;

        Carbon::setLocale('id');

        $start = $submission->tanggal_mulai ? Carbon::parse($submission->tanggal_mulai) : null;
        $end = $submission->tanggal_selesai ? Carbon::parse($submission->tanggal_selesai) : null;
        $letterDate = $submission->updated_at ?? Carbon::now();

        // Rentang bulan dihitung inklusif: Januari–Desember = 12 bulan.
        $months = ($start && $end) ? max(1, (int) $start->diffInMonths($end) + 1) : null;
        $durasiMagang = $months ? $months.' bulan' : '—';
        $bulanMulai = $start?->translatedFormat('F Y') ?? '—';
        $bulanSelesai = $end?->translatedFormat('F Y') ?? '—';
        $tanggalSurat = $letterDate->translatedFormat('d F Y');
        $filename = 'Surat_Permohonan_Magang_'.$student->nim.'.pdf';

        $pdfPath = $this->renderForm2FromDocxTemplate(
            $submission,
            $letterDate,
            $durasiMagang,
            $bulanMulai,
            $bulanSelesai,
            $tanggalSurat,
        );

        if ($pdfPath !== null) {
            return response()->download($pdfPath, $filename)->deleteFileAfterSend(true);
        }

        // Fallback ketika LibreOffice tidak tersedia: render replika HTML via dompdf.
        $pdf = Pdf::loadView('pdf.surat-pengantar-form2', [
            'submission' => $submission,
            'student' => $student,
            'letterNumber' => $this->form2LetterNumber($submission, $letterDate),
            'durasiMagang' => $durasiMagang,
            'bulanMulai' => $bulanMulai,
            'bulanSelesai' => $bulanSelesai,
            'tanggalSurat' => $tanggalSurat,
            'signatoryName' => 'Dr. Rizki Maryam Astuti., S.Si., M.Si',
            'signatoryRole' => 'Ka.UPT Pusat Pengembangan Akademik dan Inovasi Pembelajaran',
            'signatureSrc' => null,
            'letterheadSrc' => $this->assetImageSource('assets/images/form2-letterhead.png'),
            'footerSrc' => $this->assetImageSource('assets/images/form2-footer.png'),
        ])->setPaper('a4');

        return $pdf->download($filename);
    }

    /**
     * Isi placeholder pada template DOCX asli lalu konversi ke PDF via LibreOffice,
     * sehingga hasilnya identik dengan template di public/assets.
     * Mengembalikan null bila template/LibreOffice tidak tersedia.
     */
    private function renderForm2FromDocxTemplate(
        Form2Submission $submission,
        Carbon $letterDate,
        string $durasiMagang,
        string $bulanMulai,
        string $bulanSelesai,
        string $tanggalSurat,
    ): ?string {
        $template = public_path('assets/template-form-2.docx');
        $soffice = $this->libreOfficeBinary();

        if ($soffice === null || ! file_exists($template)) {
            return null;
        }

        $workDir = storage_path('app/tmp/form2-'.Str::uuid());
        File::ensureDirectoryExists($workDir);

        $docx = $workDir.'/surat.docx';
        copy($template, $docx);

        $zip = new ZipArchive;
        if ($zip->open($docx) !== true) {
            File::deleteDirectory($workDir);

            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->addFromString('word/document.xml', strtr($xml, $this->form2TemplateReplacements(
            $submission,
            $letterDate,
            $durasiMagang,
            $bulanMulai,
            $bulanSelesai,
            $tanggalSurat,
        )));
        $zip->close();

        $process = new Process([
            $soffice,
            '--headless',
            '--norestore',
            '-env:UserInstallation='.$this->fileUri($workDir.'/lo-profile'),
            '--convert-to',
            'pdf',
            '--outdir',
            $workDir,
            $docx,
        ]);
        $process->setTimeout(120);

        try {
            $process->run();
        } catch (\Throwable $e) {
            report($e);
            File::deleteDirectory($workDir);

            return null;
        }

        $converted = $workDir.'/surat.pdf';

        if (! file_exists($converted)) {
            File::deleteDirectory($workDir);

            return null;
        }

        File::ensureDirectoryExists(storage_path('app/tmp'));
        $pdfPath = storage_path('app/tmp/'.Str::uuid().'.pdf');
        File::move($converted, $pdfPath);
        File::deleteDirectory($workDir);

        return $pdfPath;
    }

    /**
     * Peta penggantian teks pada word/document.xml. Kunci berupa string
     * placeholder yang sudah ter-escape XML persis seperti di dalam template.
     */
    private function form2TemplateReplacements(
        Form2Submission $submission,
        Carbon $letterDate,
        string $durasiMagang,
        string $bulanMulai,
        string $bulanSelesai,
        string $tanggalSurat,
    ): array {
        $student = $submission->student;
        $e = fn ($value) => htmlspecialchars((string) ($value ?? '—'), ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $seq = str_pad((string) $submission->id, 2, '0', STR_PAD_LEFT);
        $roman = self::ROMAN_MONTHS[$letterDate->month] ?? $letterDate->month;

        return [
            // Nomor surat contoh "01/EXT-M/PPAIP-UB/IV/2026" terpecah dalam beberapa run.
            '<w:r w:rsidR="00A021CA"><w:t>01</w:t></w:r>' => '<w:r w:rsidR="00A021CA"><w:t>'.$e($seq).'</w:t></w:r>',
            '<w:t>UB/IV/2026</w:t>' => '<w:t>'.$e('UB/'.$roman.'/'.$letterDate->year).'</w:t>',
            '&lt;&lt;Nama Perusahaan&gt;&gt;' => $e($submission->company_name),
            '&lt;&lt;Nama Pimpinan&gt;&gt;' => $e($submission->nama_pimpinan),
            '&lt;&lt;Jabatan&gt;&gt;' => $e($submission->jabatan_pimpinan),
            '&lt;&lt;Alamat Lengkap Perusahaan&gt;&gt;' => $e($submission->alamat_perusahaan),
            '&lt;&lt;rencana bulan magang&gt;&gt;' => $e($durasiMagang),
            '&lt;&lt;bulan mulai&gt;&gt;' => $e($bulanMulai),
            '&lt;&lt;bulan selesai&gt;&gt;' => $e($bulanSelesai),
            '&lt;&lt;nama lengkap&gt;&gt;' => $e($student->name),
            '&lt;&lt;NIM&gt;&gt;' => $e($student->nim),
            '&lt;&lt;Jurusan&gt;&gt;' => $e($student->study_program),
            '&lt;&lt;tanggal &amp; bulansekarang&gt;&gt;' => $e($tanggalSurat),
        ];
    }

    private function fileUri(string $path): string
    {
        return 'file://'.implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    private function libreOfficeBinary(): ?string
    {
        $candidates = array_filter([
            config('services.libreoffice.path'),
            '/Applications/LibreOffice.app/Contents/MacOS/soffice',
            '/opt/homebrew/bin/soffice',
            '/usr/local/bin/soffice',
            '/usr/bin/soffice',
            '/usr/bin/libreoffice',
        ]);

        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function form2LetterNumber(Form2Submission $submission, Carbon $date): string
    {
        $seq = str_pad((string) $submission->id, 2, '0', STR_PAD_LEFT);
        $roman = self::ROMAN_MONTHS[$date->month] ?? $date->month;

        return $seq.'/EXT-M/PPAIP-UB/'.$roman.'/'.$date->year;
    }
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
        return $this->assetImageSource('assets/images/logo-ubakrie.png');
    }

    private function assetImageSource(string $relativePath): ?string
    {
        $path = public_path($relativePath);

        return file_exists($path) ? $this->imageDataUri($path) : null;
    }

    private function imageDataUri(string $path): string
    {
        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }
}
