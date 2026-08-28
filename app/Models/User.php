<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        // Hanya diisi untuk Staff Prodi. Kaprodi mendapat program studinya
        // dari baris dosen miliknya, bukan dari kolom ini.
        'study_program',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locked_until' => 'datetime',
            'last_failed_login_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isKaprodi(): bool
    {
        return $this->role === 'kaprodi';
    }

    public function isDpm(): bool
    {
        return $this->role === 'dpm';
    }

    public function isPpaip(): bool
    {
        return $this->role === 'ppaip';
    }

    public function isDosenPenguji(): bool
    {
        return $this->role === 'dosen_penguji';
    }

    /**
     * Staff Prodi: berperan sebagai Kaprodi, tapi bukan dosen.
     *
     * Dikenali dari ketiadaan baris dosen, bukan dari role tersendiri --
     * kewenangannya memang identik dengan Kaprodi, yang berbeda hanya
     * identitas yang tercetak di dokumen resmi.
     */
    public function isStaffProdi(): bool
    {
        return $this->isKaprodi() && $this->lecturer === null;
    }

    /**
     * Program studi yang dipegang pengguna ini.
     *
     * Kaprodi dan dosen mendapatkannya dari baris dosen miliknya. Staff Prodi
     * tidak punya baris dosen, jadi diambil dari kolom users.study_program.
     */
    public function resolveStudyProgram(): ?string
    {
        return $this->lecturer?->study_program ?? $this->study_program;
    }

    /**
     * Dosen yang namanya sah tercetak di dokumen resmi atas tindakan ini.
     *
     * Kaprodi menandatangani atas namanya sendiri. Staff Prodi bertindak atas
     * nama Kaprodi program studinya, sehingga surat tetap memuat nama dan NIDN
     * Kaprodi meski yang menekan tombol adalah staff.
     *
     * Mengembalikan null bila penandatangannya tidak bisa ditentukan secara
     * pasti -- tidak ada Kaprodi di prodi itu, atau justru ada lebih dari satu.
     * Menebak penandatangan dokumen resmi adalah mode kegagalan yang salah,
     * jadi pemanggilnya wajib menolak tindakan tersebut.
     */
    public function signatoryLecturer(): ?Lecturer
    {
        if ($this->lecturer) {
            return $this->lecturer;
        }

        $studyProgram = $this->study_program;

        if ($studyProgram === null) {
            return null;
        }

        $candidates = Lecturer::query()
            ->where('study_program', $studyProgram)
            ->whereHas('user', fn ($query) => $query->where('role', 'kaprodi'))
            ->take(2)
            ->get();

        return $candidates->count() === 1 ? $candidates->first() : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && in_array($this->role, ['kaprodi', 'dpm', 'ppaip', 'dosen_penguji'], true);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function lecturer()
    {
        return $this->hasOne(Lecturer::class);
    }
}
