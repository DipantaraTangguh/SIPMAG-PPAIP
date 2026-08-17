<?php

namespace App\Exports;

use App\Models\InternshipCycle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Ekspor rekap seluruh siklus magang yang sudah selesai. Kaprodi hanya
 * mendapat prodinya sendiri, sama seperti tampilan tabelnya.
 */
class InternshipCyclesExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping
{
    private int $rowNumber = 0;

    public function __construct(private readonly User $user) {}

    public function query(): Builder
    {
        $query = InternshipCycle::query()->orderByDesc('completed_at');

        if ($this->user->role === 'kaprodi') {
            $prodi = $this->user->lecturer?->study_program;
            $query = $prodi
                ? $query->where('study_program', $prodi)
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Semester',
            'IPK',
            'Siklus Ke-',
            'Jenis Magang',
            'Status Akhir',
            'Skema Magang',
            'Topik / Lingkup',
            'Target Output',
            'Perusahaan',
            'Alamat Perusahaan',
            'Pimpinan',
            'Mulai Magang',
            'Selesai Magang',
            'Nilai Akhir',
            'Nilai Huruf',
            'Bukti LoA',
            'Diselesaikan Pada',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        /** @var InternshipCycle $row */
        return [
            ++$this->rowNumber,
            $row->nim,
            $row->nama,
            $row->study_program,
            $row->semester ?? '-',
            $row->ipk !== null ? (float) $row->ipk : null,
            $row->cycle_number,
            $row->jenis_magang === 'wajib' ? 'Wajib' : 'Non-Wajib',
            $row->outcome_status === 'CycleCompleted' ? 'Selesai (Sidang & Penilaian)' : 'Selesai (Non-Wajib)',
            $row->skema_magang ?? '-',
            $row->topik_magang ?? '-',
            $row->output_target ?? '-',
            $row->company_name ?? '-',
            $row->alamat_perusahaan ?? '-',
            $row->nama_pimpinan ?? '-',
            $row->tanggal_mulai?->format('m/Y') ?? '-',
            $row->tanggal_selesai?->format('m/Y') ?? '-',
            $row->final_score !== null ? (float) $row->final_score : null,
            $row->letter_grade ?? '-',
            $this->loaCell($row),
            $row->completed_at?->format('d/m/Y H:i') ?? '-',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'P' => NumberFormat::FORMAT_TEXT,
            'Q' => NumberFormat::FORMAT_TEXT,
            'R' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    private function loaCell(InternshipCycle $cycle): string
    {
        if (! $cycle->loa_path) {
            return '-';
        }

        $url = route('rekap-magang.loa.download', $cycle);

        return '=HYPERLINK("'.$this->escapeFormulaString($url).'","'.$this->escapeFormulaString(basename($cycle->loa_path)).'")';
    }

    private function escapeFormulaString(string $value): string
    {
        return str_replace('"', '""', $value);
    }
}
