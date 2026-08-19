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
     * State yang berarti tempat magang sudah benar-benar didapat, sehingga
     * mahasiswa tidak boleh melamar lowongan mitra maupun mengajukan Form 2.
     *
     * AwaitingConfirmation sengaja TIDAK masuk: non-wajib berada di state itu
     * sejak Form 1 disetujui, justru saat belum mengonfirmasi diterima di mana
     * pun.
     *
     * Daftar ini harus sama dengan SECURED_INTERNSHIP_STATUSES di
     * accessUtils.js -- dulu dua salinan di controller sempat beda dan bikin
     * portal menolak diam-diam. Pesan penolakannya beda per konteks, jadi
     * tetap tinggal di controller masing-masing.
     *
     * @var array<int, string>
     */
    public const SECURED_INTERNSHIP = [
        'HasDPM',
        'LogbookComplete',
        'AwaitingDefense',
        'CycleCompleted',
        'ElectiveCompleted',
    ];

    /**
     * True bila mahasiswa sudah mengamankan tempat magang. State null
     * (mis. profil mahasiswa belum ada) dianggap belum mengamankan apa pun.
     */
    public static function hasSecuredInternship(?string $state): bool
    {
        return in_array($state, self::SECURED_INTERNSHIP, true);
    }

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
