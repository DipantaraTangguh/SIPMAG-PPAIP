<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Mengisi placeholder teks pada template DOCX lalu mengonversinya ke PDF
 * melalui LibreOffice headless, sehingga hasil PDF identik dengan template.
 */
class DocxToPdfRenderer
{
    /**
     * @param  array<string, mixed>  $replacements  Peta placeholder persis seperti di template
     *                                              (mis. '<<NIM>>') ke nilai isiannya; escaping XML
     *                                              untuk keduanya ditangani di sini.
     * @return string|null Path PDF sementara (hapus setelah dikirim), atau null bila
     *                     template/LibreOffice tidak tersedia atau konversi gagal.
     */
    public function render(string $templatePath, array $replacements): ?string
    {
        $soffice = $this->libreOfficeBinary();

        if ($soffice === null || ! file_exists($templatePath)) {
            return null;
        }

        $workDir = storage_path('app/tmp/docx-'.Str::uuid());
        File::ensureDirectoryExists($workDir);

        try {
            $docx = $workDir.'/document.docx';
            copy($templatePath, $docx);
            $this->fillPlaceholders($docx, $replacements);

            return $this->convertToPdf($soffice, $workDir, $docx);
        } catch (\Throwable $e) {
            report($e);

            return null;
        } finally {
            File::deleteDirectory($workDir);
        }
    }

    /**
     * @param  array<string, mixed>  $replacements
     */
    private function fillPlaceholders(string $docx, array $replacements): void
    {
        $escaped = [];

        foreach ($replacements as $placeholder => $value) {
            $escaped[$this->xmlEscape($placeholder)] = $this->xmlEscape($value);
        }

        $zip = new ZipArchive;

        if ($zip->open($docx) !== true) {
            throw new \RuntimeException('Gagal membuka template DOCX: '.$docx);
        }

        $zip->addFromString('word/document.xml', strtr($zip->getFromName('word/document.xml'), $escaped));
        $zip->close();
    }

    private function convertToPdf(string $soffice, string $workDir, string $docx): ?string
    {
        $process = new Process([
            $soffice,
            '--headless',
            '--norestore',
            '-env:UserInstallation='.$this->fileUri($workDir.'/lo-profile'),
            '--convert-to', 'pdf',
            '--outdir', $workDir,
            $docx,
        ]);
        $process->setTimeout(120);
        $process->run();

        $converted = $workDir.'/document.pdf';

        if (! file_exists($converted)) {
            return null;
        }

        // Pindahkan keluar dari workDir agar tetap ada setelah workDir dibersihkan.
        $pdfPath = storage_path('app/tmp/'.Str::uuid().'.pdf');
        File::move($converted, $pdfPath);

        return $pdfPath;
    }

    private function xmlEscape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Path proyek bisa mengandung spasi, jadi URI wajib di-encode
     * (LibreOffice crash bila UserInstallation berisi spasi mentah).
     */
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
}
