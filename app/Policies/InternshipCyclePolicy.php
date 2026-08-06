<?php

namespace App\Policies;

use App\Models\InternshipCycle;
use App\Models\User;

/**
 * Rekap riwayat magang: PPAIP lihat semua prodi, Kaprodi hanya prodinya
 * sendiri. Dipakai resource Filament sekaligus rute ekspor & berkas LoA.
 */
class InternshipCyclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPpaip() || $this->isKaprodiWithProdi($user);
    }

    public function view(User $user, InternshipCycle $cycle): bool
    {
        if ($user->isPpaip()) {
            return true;
        }

        // Cocokkan ke snapshot prodi di baris riwayat, bukan prodi mahasiswa
        // saat ini -- riwayat harus tetap konsisten walau datanya berubah.
        return $this->isKaprodiWithProdi($user)
            && $user->lecturer->study_program === $cycle->study_program;
    }

    private function isKaprodiWithProdi(User $user): bool
    {
        return $user->role === 'kaprodi' && $user->lecturer?->study_program !== null;
    }
}
