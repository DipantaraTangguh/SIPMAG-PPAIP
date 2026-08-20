<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Support\StudyProgram;
use Illuminate\Database\Seeder;

/**
 * Utilitas sekali pakai: menghapus (soft delete) 100 mahasiswa demo yang
 * dulu dibuat DemoStudentsSeeder, supaya tidak bercampur dengan peserta
 * uji coba sungguhan di TestingAccountsSeeder.
 *
 * NIM demo dihitung ulang persis dengan algoritma DemoStudentsSeeder
 * (pola 2026PPNN) sehingga baris yang dihapus dijamin sama dengan yang
 * seeder itu buat -- tidak ada mahasiswa lain yang tersentuh.
 *
 * Soft delete, bukan hard delete: beberapa tabel (applications,
 * form2_submissions, logbooks, dst) mengunci student_id dengan
 * restrictOnDelete, dan baris mahasiswa demo di production mungkin
 * sudah punya data terkait dari sesi uji coba sebelumnya. Soft delete
 * cukup untuk menyembunyikannya dari semua listing (Eloquent otomatis
 * memfilter baris terhapus), tanpa risiko gagal karena foreign key.
 *
 *   php artisan db:seed --class=PurgeDemoStudentsSeeder --force
 */
class PurgeDemoStudentsSeeder extends Seeder
{
    private const PER_PROGRAM = 10;

    public function run(): void
    {
        $nims = $this->demoNims();

        $students = Student::whereIn('nim', $nims)->get();
        $userIds = $students->pluck('user_id');

        $deletedStudents = 0;
        foreach ($students as $student) {
            $student->delete();
            $deletedStudents++;
        }

        $deletedUsers = User::whereIn('id', $userIds)
            ->where('role', 'mahasiswa')
            ->get()
            ->each(fn (User $user) => $user->delete())
            ->count();

        $this->command?->info("Mahasiswa demo dihapus: {$deletedStudents} student, {$deletedUsers} user (dari ".count($nims).' NIM demo yang dicari).');
    }

    /**
     * @return array<int, string>
     */
    private function demoNims(): array
    {
        $nims = [];

        foreach (array_values(StudyProgram::ALL) as $programIndex => $program) {
            $programNumber = str_pad((string) ($programIndex + 1), 2, '0', STR_PAD_LEFT);

            for ($i = 1; $i <= self::PER_PROGRAM; $i++) {
                $sequence = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $nims[] = "2026{$programNumber}{$sequence}";
            }
        }

        return $nims;
    }
}
