<?php

namespace App\Support;

/**
 * Terjemahan students.access_status untuk ditampilkan di panel admin.
 *
 * Nilai internalnya tetap bahasa Inggris (lihat StudentStateMachine); yang
 * diterjemahkan hanya labelnya, supaya PPAIP dan Kaprodi membaca istilah
 * yang wajar alih-alih nama state mentah seperti "AwaitingDefense".
 *
 * Satu-satunya sumber label dan warna badge, dipakai semua Filament resource
 * supaya tidak ada dua tempat yang bisa berbeda.
 */
class AccessStatus
{
    /** @var array<string, string> */
    public const LABELS = [
        'Unverified' => 'Belum Verifikasi',
        'PendingReview' => 'Menunggu Review',
        'RejectedForm1' => 'Form 1 Ditolak',
        'ApprovedForm1' => 'Form 1 Disetujui',
        'HasApplication' => 'Sudah Melamar',
        'HasDPM' => 'Sudah Ada DPM',
        'LogbookComplete' => 'Logbook Lengkap',
        'AwaitingDefense' => 'Menunggu Sidang',
        'CycleCompleted' => 'Siklus Selesai',
        'ElectiveCompleted' => 'Selesai (Non-Wajib)',
        'AwaitingConfirmation' => 'Menunggu Konfirmasi',
    ];

    /** @var array<string, string> */
    private const COLORS = [
        'Unverified' => 'gray',
        'PendingReview' => 'warning',
        'RejectedForm1' => 'danger',
        'ApprovedForm1' => 'success',
        'HasApplication' => 'info',
        'HasDPM' => 'primary',
        'LogbookComplete' => 'success',
        'AwaitingDefense' => 'warning',
        'CycleCompleted' => 'success',
        'ElectiveCompleted' => 'success',
        'AwaitingConfirmation' => 'warning',
    ];

    /**
     * Label Indonesia untuk satu state. State tak dikenal dikembalikan apa
     * adanya supaya tidak ada data yang "hilang" dari layar.
     */
    public static function label(?string $state): string
    {
        if ($state === null) {
            return '-';
        }

        return self::LABELS[$state] ?? $state;
    }

    public static function color(?string $state): string
    {
        return self::COLORS[$state] ?? 'gray';
    }

    /**
     * Opsi untuk SelectFilter / Select: value tetap Inggris, label Indonesia.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::LABELS;
    }
}
