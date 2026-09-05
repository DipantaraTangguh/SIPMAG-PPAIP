<?php

namespace App\Exports;

use App\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MitraApplicantsExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping
{
    private int $rowNumber = 0;

    public function query(): Builder
    {
        return Application::query()
            ->with([
                'student:id,nim,name,study_program,email,semester,jumlah_sks,ipk,access_status',
                'internship:id,company_name,position,location,deadline',
            ])
            ->orderByDesc('created_at');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal Lamar',
            'Status Lamaran',
            'NIM',
            'Nama Mahasiswa',
            'Email',
            'Program Studi',
            'Semester',
            'Jumlah SKS',
            'IPK',
            'Status Mahasiswa',
            'Perusahaan',
            'Posisi',
            'Lokasi',
            'Deadline',
            'CV',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        /** @var Application $row */
        $student = $row->student;
        $internship = $row->internship;

        return [
            ++$this->rowNumber,
            $row->created_at?->format('d/m/Y H:i') ?? '-',
            $row->status,
            $student?->nim ?? '-',
            $student?->name ?? '-',
            $student?->email ?? '-',
            $student?->study_program ?? '-',
            $student?->semester ?? '-',
            $student?->jumlah_sks ?? '-',
            $student?->ipk !== null ? (float) $student->ipk : null,
            $student?->access_status ?? '-',
            $internship?->company_name ?? '-',
            $internship?->position ?? '-',
            $internship?->location ?? '-',
            $internship?->deadline?->format('d/m/Y') ?? '-',
            $this->cvCell($row),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'O' => NumberFormat::FORMAT_TEXT,
        ];
    }

    private function cvCell(Application $application): string
    {
        if (! $application->cv_file_path) {
            return '-';
        }

        $url = route('partner-applications.cv.download', $application);
        $filename = basename($application->cv_file_path);

        return '=HYPERLINK("'.$this->escapeFormulaString($url).'","'.$this->escapeFormulaString($filename).'")';
    }

    private function escapeFormulaString(string $value): string
    {
        return str_replace('"', '""', $value);
    }
}
