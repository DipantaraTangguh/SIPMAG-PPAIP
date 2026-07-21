<?php

namespace App\Services;

use App\Models\Form2Submission;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class PdfService
{
    private const ROMAN_MONTHS = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    public function __construct(private DocxToPdfRenderer $docxRenderer)
    {
    }

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

        $pdfPath = $this->docxRenderer->render(public_path('assets/template-form-2.docx'), [
            '<<Nomor Surat>>' => $this->form2LetterNumber($submission, $letterDate),
            '<<Nama Perusahaan>>' => $submission->company_name,
            '<<Nama Pimpinan>>' => $submission->nama_pimpinan ?? '-',
            '<<Jabatan>>' => $submission->jabatan_pimpinan ?? '-',
            '<<Alamat Lengkap Perusahaan>>' => $submission->alamat_perusahaan,
            '<<rencana bulan magang>>' => $months ? $months.' bulan' : '-',
            '<<bulan mulai>>' => $start?->translatedFormat('F Y') ?? '-',
            '<<bulan selesai>>' => $end?->translatedFormat('F Y') ?? '-',
            '<<nama lengkap>>' => $student->name,
            '<<NIM>>' => $student->nim,
            '<<Jurusan>>' => $student->study_program,
            '<<tanggal & bulansekarang>>' => $letterDate->translatedFormat('d F Y'),
        ]);

        abort_if($pdfPath === null, 503, 'Konversi PDF tidak tersedia. Pastikan LibreOffice terpasang atau atur LIBREOFFICE_PATH.');

        return response()
            ->download($pdfPath, 'Surat_Permohonan_Magang_'.$student->nim.'.pdf')
            ->deleteFileAfterSend(true);
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
        $form1 = $student->form1_data ?? [];

        Carbon::setLocale('id');

        $pdfPath = $this->docxRenderer->render(public_path('assets/template-form-1.docx'), [
            '<<Nama Kaprodi>>' => $kaprodi->lecturer_name ?? '-',
            '<<Program Studi>>' => $student->study_program ?? '-',
            '<<Nama Mahasiswa>>' => $student->name,
            '<<NIM>>' => $student->nim,
            '<<Semester>>' => (string) ($form1['semester'] ?? $student->semester ?? '-'),
            '<<Jumlah SKS>>' => (string) ($form1['jumlahSKS'] ?? $student->jumlah_sks ?? '-'),
            '<<IPK>>' => (string) ($form1['ipk'] ?? $student->ipk ?? '-'),
            '<<Rencana Magang>>' => $form1['skemaMagang'] ?? '-',
            '<<Tanggal Pengajuan>>' => optional($student->updated_at)->translatedFormat('d F Y') ?? '-',
            '<<Tanggal Persetujuan>>' => optional($student->form1_approved_at)->translatedFormat('d F Y') ?? '-',
            '<<NIDN>>' => $kaprodi->nidn ?? '-',
        ]);

        abort_if($pdfPath === null, 503, 'Konversi PDF tidak tersedia. Pastikan LibreOffice terpasang atau atur LIBREOFFICE_PATH.');

        return response()
            ->download($pdfPath, 'Surat_Keterangan_Form1_'.$student->nim.'.pdf')
            ->deleteFileAfterSend(true);
    }
}
