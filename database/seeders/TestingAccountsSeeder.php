<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Akun untuk sesi uji coba: 20 mahasiswa peserta + 1 Kaprodi untuk tiap
 * program studi. Akun PPAIP tidak disentuh -- tetap pakai yang lama.
 *
 * Aman dijalankan di database yang sudah berisi data:
 * - Idempotent, dicocokkan lewat NIM (mahasiswa) dan program studi (Kaprodi).
 * - access_status hanya diisi saat baris mahasiswa BARU dibuat, supaya
 *   menjalankan ulang seeder di tengah pengujian tidak menghapus progres.
 * - NIDN dan tanda tangan Kaprodi yang sudah ada tidak ditimpa.
 *
 *   php artisan db:seed --class=TestingAccountsSeeder --force
 */
class TestingAccountsSeeder extends Seeder
{
    private const PASSWORD = 'password';

    private const EMAIL_DOMAIN = '@bakrie.ac.id';

    private const STUDENT_EMAIL_DOMAIN = '@student.bakrie.ac.id';

    /**
     * Satu Kaprodi per program studi: [program studi, nama, email, NIDN].
     *
     * NIDN di sini placeholder berformat valid dan hanya dipakai saat baris
     * dosennya belum ada. Ganti lewat panel admin kalau sudah dapat yang asli.
     *
     * @var array<int, array{0: string, 1: string, 2: string, 3: string}>
     */
    private const KAPRODI = [
        ['Manajemen', 'Prof. M. Taufiq Amir, S.E., M.M., Ph.D.', 'taufiq.amir', '0301017001'],
        ['Akuntansi', 'Monica Weni Pratiwi, SE., M.Si.', 'monica.pratiwi', '0302027502'],
        ['Ilmu Politik', 'Dr. Rer. Pol. Aditya Batara Gunawan, S.Sos., M.Litt.', 'aditya.gunawan', '0303038003'],
        ['Ilmu Komunikasi', 'Dra. Suharyanti, M.S.M., Ph.D.', 'suharyanti', '0304046504'],
        ['Informatika', 'Ir. Iwan Adhicandra, S.T., M.Sc., Ph.D., SMIEEE', 'iwan.adhicandra', '0305057005'],
        ['Sistem Informasi', 'Prof. Dr. Hoga Saragih, S.T., M.T, IPM., CIRR., MIEEE., M.Th, Ph.D.', 'hoga.saragih', '0422117502'],
        ['Teknik Industri', 'Edo Suryo Pratomo, S.T., M.Sc., Ph.D., CAMF', 'edo.pratomo', '0307078507'],
        ['Teknik Sipil', 'Fatin Adriati, S.T., M.T., IPP.', 'fatin.adriati', '0308088208'],
        ['Teknik Lingkungan', 'Ir. Aqil Azizi, M.ApplSc., Ph.D., GP., IPM', 'aqil.azizi', '0309097209'],
        ['Ilmu & Teknologi Pangan', 'Kurnia Ramadhan, S.TP., M.Sc., Ph.D.', 'kurnia.ramadhan', '0310108010'],
    ];

    /**
     * Peserta uji coba: [NIM, nama lengkap, program studi].
     *
     * Nama program studi dipetakan ke ejaan resmi di App\Support\StudyProgram
     * -- "Teknik Informatika" menjadi "Informatika" dan "Ilmu dan Teknologi
     * Pangan" menjadi "Ilmu & Teknologi Pangan" -- supaya cocok dengan filter
     * Kaprodi dan pembatasan program studi di lowongan mitra.
     *
     * @var array<int, array{0: string, 1: string, 2: string}>
     */
    private const STUDENTS = [
        ['1231001162', 'Muchammad Juldan', 'Manajemen'],
        ['1231001083', 'Andrea Nanda Hafidz', 'Manajemen'],
        ['1231001053', 'Rasya Asri Putri', 'Manajemen'],
        ['1231001082', 'Syafira Juliaintani Chaniago', 'Manajemen'],
        ['1231001073', 'Cahyo Utomo', 'Manajemen'],
        ['1231001143', 'Indra Saputra', 'Manajemen'],
        ['1231001103', 'Riza Pahlevi', 'Manajemen'],
        ['1231001163', 'Arjuna Munandar', 'Manajemen'],
        ['1231002055', 'Muhammad Alif Fahmi', 'Akuntansi'],
        ['1222006009', 'Angel Carolin Aldaloma', 'Ilmu & Teknologi Pangan'],
        ['1232004014', 'Hadits Ardhiansyah', 'Teknik Sipil'],
        ['1232001031', 'Bagus Arya Maulana', 'Informatika'],
        ['1232005012', 'Nazila Kuraeni', 'Teknik Lingkungan'],
        ['1232005020', 'Kaysa Mutia Salsabil', 'Teknik Lingkungan'],
        ['1231004106', 'Muhammad Sahal', 'Ilmu Politik'],
        ['1232003026', 'Alwasyah', 'Teknik Industri'],
        ['1232003039', 'Darren Jethrovick', 'Teknik Industri'],
        ['1231002008', 'Micko Ardiansyah', 'Akuntansi'],
        ['1232002087', 'Achmad Taufik Alfarizy', 'Sistem Informasi'],
        ['1232002056', 'Abshina Attar Kaur', 'Sistem Informasi'],
    ];

    public function run(): void
    {
        $this->seedKaprodi();
        $this->seedStudents();
    }

    private function seedKaprodi(): void
    {
        $password = Hash::make(self::PASSWORD);

        foreach (self::KAPRODI as [$program, $name, $emailPrefix, $nidn]) {
            $email = $emailPrefix.self::EMAIL_DOMAIN;

            $user = User::firstOrNew(['email' => $email]);
            $user->fill(['name' => $name, 'role' => 'kaprodi']);
            if (! $user->exists) {
                $user->password = $password;
            }
            $user->save();

            // Satu program studi cukup satu Kaprodi. Kalau sudah ada dosen
            // Kaprodi untuk prodi ini -- termasuk Kaprodi contoh bawaan -- baris
            // itu yang dipakai ulang, bukan bikin duplikat baru.
            $lecturer = Lecturer::query()
                ->where('study_program', $program)
                ->whereHas('user', fn ($query) => $query->where('role', 'kaprodi'))
                ->first()
                ?? new Lecturer(['nidn' => $nidn]);

            // Lecturer dipindah ke user Kaprodi yang baru -- user lama (kalau
            // beda dan memang Kaprodi contoh bawaan) jadi yatim piatu tanpa
            // prodi. Hapus supaya tidak ada kredensial nyasar yang login-nya
            // hidup tapi tidak terhubung ke prodi manapun.
            $previousUserId = $lecturer->exists ? $lecturer->user_id : null;
            if ($previousUserId && $previousUserId !== $user->id) {
                User::where('id', $previousUserId)->where('role', 'kaprodi')->delete();
            }

            $lecturer->fill([
                'user_id' => $user->id,
                'lecturer_name' => $name,
                'contact' => $email,
                'study_program' => $program,
            ])->save();
        }

        $this->command?->info('Kaprodi siap: '.count(self::KAPRODI).' program studi.');
    }

    private function seedStudents(): void
    {
        $password = Hash::make(self::PASSWORD);
        $rows = [];

        foreach (self::STUDENTS as [$nim, $name, $program]) {
            $email = $this->studentEmail($name);

            $user = User::firstOrNew(['email' => $email]);
            $user->fill(['name' => $name, 'role' => 'mahasiswa']);
            if (! $user->exists) {
                $user->password = $password;
            }
            $user->save();

            $student = Student::firstOrNew(['nim' => $nim]);
            $student->fill([
                'user_id' => $user->id,
                'name' => $name,
                'study_program' => $program,
                'email' => $email,
                'semester' => 7,
                'tahun_akademik' => '2026/2027',
                // Di atas syarat minimal Form 1 (85 SKS) supaya peserta bisa
                // langsung mengajukan tanpa dibetulkan dulu lewat panel admin.
                'jumlah_sks' => 120,
                'ipk' => 3.50,
            ]);

            if (! $student->exists) {
                $student->is_independent = false;
                $student->access_status = 'Unverified';
            }

            $student->save();

            $rows[] = [$nim, $name, $program, $email];
        }

        $this->command?->info('Peserta uji coba siap: '.count($rows).' mahasiswa. Kata sandi semua akun: '.self::PASSWORD);
        $this->command?->table(['NIM (untuk login)', 'Nama', 'Program Studi', 'Email'], $rows);
    }

    /**
     * Email dari kata depan dan kata terakhir nama, misalnya
     * "Syafira Juliaintani Chaniago" menjadi syafira.chaniago@…
     */
    private function studentEmail(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        $parts = count($words) > 1 ? [reset($words), end($words)] : [reset($words)];

        return Str::slug(implode(' ', $parts), '.').self::STUDENT_EMAIL_DOMAIN;
    }
}
