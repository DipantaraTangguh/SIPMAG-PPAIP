<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Dpm\DpmLogbookResource;
use App\Filament\Resources\Kaprodi\KaprodiDefenseScheduleResource;
use App\Filament\Resources\Kaprodi\KaprodiDpmAssignmentResource;
use App\Filament\Resources\Kaprodi\KaprodiForm1ReviewResource;
use App\Filament\Resources\Kaprodi\KaprodiStudentResource;
use App\Filament\Resources\Penguji\ExaminedSessionResource;
use App\Filament\Resources\Ppaip\PpaipForm2Resource;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

/**
 * Ringkasan pekerjaan di halaman depan panel.
 *
 * Sebelumnya dashboard hanya memuat kartu sapaan bawaan Filament, jadi siapa
 * pun yang masuk mendarat di halaman yang tidak mengatakan apa-apa dan harus
 * menebak sendiri sisi mana yang menunggu dikerjakan.
 *
 * Angka-angkanya tidak dihitung ulang di sini. Semuanya memanggil method yang
 * sama dengan badge di sidebar, supaya kartu dan badge tidak mungkin
 * menampilkan angka yang berbeda untuk pekerjaan yang sama.
 */
class RoleWorkloadOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    /**
     * Polling dimatikan. Bawaannya lima detik sekali, padahal angka-angka ini
     * berubah paling sering beberapa kali sehari -- itu berarti lima query
     * berulang tiap lima detik untuk setiap dashboard yang terbuka. Badge di
     * sidebar juga tidak menyegarkan diri, jadi mematikannya sekaligus
     * membuat keduanya tidak pernah tampak berbeda di layar yang sama.
     */
    protected static ?string $pollingInterval = null;

    /**
     * Dirender langsung bersama halaman, bukan lewat permintaan Livewire
     * susulan. Bawaan Filament menunda render dan mengandalkan polling untuk
     * mengisinya -- begitu polling dimatikan, kartunya tidak pernah muncul.
     * Isinya cuma beberapa query hitung, jadi tidak perlu ditunda.
     */
    protected static bool $isLazy = false;

    public static function canView(): bool
    {
        return static::stats() !== [];
    }

    protected function getStats(): array
    {
        return static::stats();
    }

    /**
     * @return array<int, Stat>
     */
    private static function stats(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return [];
        }

        // Penguji dan DPM sama-sama menilai sidang, jadi keduanya diperiksa
        // lewat satu cabang; DPM mendapat kartu logbooknya secara terpisah.
        return match ($user->role) {
            'kaprodi' => static::kaprodiStats(),
            'ppaip' => static::ppaipStats(),
            'dpm' => static::dpmStats(),
            'dosen_penguji' => static::pengujiStats(),
            default => [],
        };
    }

    /**
     * @return array<int, Stat>
     */
    private static function kaprodiStats(): array
    {
        return [
            static::pekerjaan(
                'Form 1 menunggu review',
                KaprodiStudentResource::pileCount(KaprodiStudentResource::whereNeedsForm1Review(...)),
                'Setujui atau tolak kelayakan akademik',
                'heroicon-o-document-check',
                KaprodiForm1ReviewResource::getUrl(),
            ),
            static::pekerjaan(
                'Menunggu penunjukan DPM',
                KaprodiStudentResource::pileCount(KaprodiStudentResource::whereNeedsDpm(...)),
                'Pengajuan pembimbing sudah masuk',
                'heroicon-o-user-plus',
                KaprodiDpmAssignmentResource::getUrl(),
            ),
            static::pekerjaan(
                'Menunggu jadwal sidang',
                KaprodiStudentResource::pileCount(KaprodiStudentResource::whereNeedsDefenseSchedule(...)),
                'Berkas sidang sudah lengkap',
                'heroicon-o-calendar-days',
                KaprodiDefenseScheduleResource::getUrl(),
            ),
        ];
    }

    /**
     * @return array<int, Stat>
     */
    private static function ppaipStats(): array
    {
        return [
            static::pekerjaan(
                'Form 2 menunggu keputusan',
                PpaipForm2Resource::pendingCount(),
                'Pengajuan magang mandiri',
                'heroicon-o-document-text',
                PpaipForm2Resource::getUrl(),
            ),
        ];
    }

    /**
     * @return array<int, Stat>
     */
    private static function dpmStats(): array
    {
        return [
            static::pekerjaan(
                'Mahasiswa dengan logbook tertunda',
                DpmLogbookResource::pendingCount(),
                'Dihitung per mahasiswa, bukan per entri',
                'heroicon-o-book-open',
                DpmLogbookResource::getUrl(),
            ),
            ...static::pengujiStats(),
        ];
    }

    /**
     * @return array<int, Stat>
     */
    private static function pengujiStats(): array
    {
        if (! ExaminedSessionResource::canAccess()) {
            return [];
        }

        return [
            static::pekerjaan(
                'Sidang menunggu penilaian Anda',
                ExaminedSessionResource::pendingCount(),
                'Sudah terjadwal dan belum Anda nilai',
                'heroicon-o-academic-cap',
                ExaminedSessionResource::getUrl(),
            ),
        ];
    }

    /**
     * Kartu hijau saat kosong, kuning saat ada yang menunggu. Warnanya sengaja
     * mengikuti badge sidebar yang juga memakai warning.
     */
    private static function pekerjaan(string $label, int $jumlah, string $keterangan, string $ikon, string $url): Stat
    {
        return Stat::make($label, $jumlah)
            ->description($jumlah > 0 ? $keterangan : 'Tidak ada yang menunggu')
            ->descriptionIcon($ikon)
            ->color($jumlah > 0 ? 'warning' : 'success')
            ->url($url);
    }
}
