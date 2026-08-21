<?php

namespace Database\Seeders;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Akun untuk sesi uji coba: 20 mahasiswa peserta, 1 Kaprodi tiap program
 * studi, dan 3 dosen tiap program studi (1 pembimbing/DPM + 2 penguji).
 * Akun PPAIP tidak disentuh -- tetap pakai yang lama.
 *
 * Aman dijalankan di database yang sudah berisi data:
 * - Idempotent, dicocokkan lewat NIM (mahasiswa), program studi (Kaprodi),
 *   dan email/user_id (dosen).
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

    /**
     * Tiga dosen per program studi: pembimbing (DPM) lalu dua penguji.
     * [program studi, nama, role, awalan email].
     *
     * role 'dpm' -> muncul di pilihan "Pilih DPM" Kaprodi. role
     * 'dosen_penguji' -> muncul di pilihan Dosen Penguji 1/2 saat
     * menjadwalkan sidang. Beda dari Kaprodi: tidak ada batas satu per
     * prodi, jadi tidak perlu logika reuse/hapus akun lama.
     *
     * Dua nama ("Dita Nurmadewi", "Maya Puspita") sama persis dengan akun
     * dosen demo bawaan (dari LecturerSeeder, Sistem Informasi) -- sengaja
     * dijadikan satu identitas, bukan dibuat akun baru. Awalan emailnya
     * dibuat sama dengan email lama supaya seedDosen() menemukan dan
     * memakai ulang baris Lecturer itu (lihat catatan di seedDosen()).
     *
     * @var array<int, array{0: string, 1: string, 2: string, 3: string}>
     */
    private const DOSEN = [
        ['Manajemen', 'Prof. Muchsin Saggaff Shihab, S.E., M.Sc., MBA, Ph.D.', 'dpm', 'muchsin.shihab'],
        ['Manajemen', 'Ananda Fortunisa, SE., MSi.', 'dosen_penguji', 'ananda.fortunisa'],
        ['Manajemen', 'Ir. Aurino Rilman A. Djamaris, MM', 'dosen_penguji', 'aurino.djamaris'],

        ['Akuntansi', 'Drs. Tri Pujadi Susilo, S.E., M.M., Ak., CA', 'dpm', 'tri.susilo'],
        ['Akuntansi', 'Dr. Jurica Lucyanda, SE, M.Si.', 'dosen_penguji', 'jurica.lucyanda'],
        ['Akuntansi', 'Dr. Tita Djuitaningsih, SE., M.Si., Ak., CA', 'dosen_penguji', 'tita.djuitaningsih'],

        ['Ilmu Komunikasi', 'Mirana Hanathasia, S.Sos., M. Media Prac.', 'dpm', 'mirana.hanathasia'],
        ['Ilmu Komunikasi', 'Dr. Dessy Kania, B.A., M.A.', 'dosen_penguji', 'dessy.kania'],
        ['Ilmu Komunikasi', 'Dianingtyas Murtanti Putri, S.Sos., M.Si.', 'dosen_penguji', 'dianingtyas.putri'],

        ['Ilmu Politik', 'Asmiati Malik, Ph.D.', 'dpm', 'asmiati.malik'],
        ['Ilmu Politik', 'Dr. M. Tri Andika Kurniawan, S.Sos., M.A.', 'dosen_penguji', 'tri.kurniawan'],
        ['Ilmu Politik', 'Dr. Bambang Sukma Wijaya', 'dosen_penguji', 'bambang.wijaya'],

        ['Informatika', 'Berkah Iman Santoso, S.T., M.T.I., MIEEE', 'dpm', 'berkah.santoso'],
        ['Informatika', 'Guson P. Kuntarto, S.T., M.Sc., MACM', 'dosen_penguji', 'guson.kuntarto'],
        ['Informatika', 'Albert Arapenta Sembiring, S.T., M.Kom, MIEEE', 'dosen_penguji', 'albert.sembiring'],

        ['Sistem Informasi', 'Dita Nurmadewi S.Kom, M.Kom', 'dpm', 'dita.nurmadewi'],
        ['Sistem Informasi', 'Zakiul Fahmi Jailani S.Kom, M.Kom', 'dosen_penguji', 'zakiul.jailani'],
        ['Sistem Informasi', 'Haris Rafi S.Kom, M.Kom', 'dosen_penguji', 'haris.rafi'],

        ['Teknik Industri', 'Mirsa Diah Novianti, S.T., M.T.', 'dpm', 'mirsa.novianti'],
        ['Teknik Industri', 'Arief Bimantoro Suharko, Ph.D.', 'dosen_penguji', 'arief.suharko'],
        ['Teknik Industri', 'Maya Puspita, PhD.', 'dosen_penguji', 'maya.puspita'],

        ['Teknik Sipil', 'Jouvan Chandra Pratama Putra, S.T., M.Eng.', 'dpm', 'jouvan.putra'],
        ['Teknik Sipil', 'Safrilah, S.T., M.Sc., IPP.', 'dosen_penguji', 'safrilah'],
        ['Teknik Sipil', 'Bima S.T', 'dosen_penguji', 'bima'],

        ['Teknik Lingkungan', 'Prof. Deffi Ayu Puspito Sari, S.T., M.Agr.Sc., Ph.D., IPM., ASEAN Eng.', 'dpm', 'deffi.sari'],
        ['Teknik Lingkungan', 'Prof Siti S.T', 'dosen_penguji', 'siti.lingkungan'],
        ['Teknik Lingkungan', 'Prof Faiz S.T', 'dosen_penguji', 'faiz.lingkungan'],

        ['Ilmu & Teknologi Pangan', 'Prof. Ardiansyah, S.TP., M.Si., Ph.D.', 'dpm', 'ardiansyah'],
        ['Ilmu & Teknologi Pangan', 'Dr.agr. Wahyudi David, S.TP., M.Sc.', 'dosen_penguji', 'wahyudi.david'],
        ['Ilmu & Teknologi Pangan', 'Dr. Rizki Maryam Astuti, S.Si., M.Si.', 'dosen_penguji', 'rizki.astuti'],
    ];

    /**
     * Email dosen yang sempat dibuat sebagai identitas terpisah (versi
     * seeder sebelumnya, sebelum "Dita Nurmadewi" dan "Maya Puspita"
     * digabung jadi satu akun dengan dosen bawaan bernama sama). Kalau
     * sempat ke-seed di database manapun, dibersihkan supaya tidak ada
     * dosen yang kelihatan dobel.
     *
     * @var array<int, string>
     */
    private const SUPERSEDED_DOSEN_EMAILS = [
        'dita.nurmadewi.dpm@bakrie.ac.id',
        'maya.puspita.penguji@bakrie.ac.id',
    ];

    public function run(): void
    {
        $this->cleanupSupersededDosen();
        $this->seedKaprodi();
        $this->seedDosen();
        $this->seedStudents();
    }

    private function cleanupSupersededDosen(): void
    {
        // Lecturer tidak pakai soft delete, jadi ini hard delete -- aman
        // karena email ini cuma pernah dipakai baris dosen yang sekarang
        // digabung, belum pernah ditunjuk jadi DPM atau penguji sidang.
        foreach (User::whereIn('email', self::SUPERSEDED_DOSEN_EMAILS)->get() as $user) {
            Lecturer::where('user_id', $user->id)->delete();
            $user->delete();
        }
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

    private function seedDosen(): void
    {
        $password = Hash::make(self::PASSWORD);
        $rows = [];

        foreach (self::DOSEN as $index => [$program, $name, $role, $emailPrefix]) {
            $email = $emailPrefix.self::EMAIL_DOMAIN;

            $user = User::firstOrNew(['email' => $email]);
            $user->fill(['name' => $name, 'role' => $role]);
            if (! $user->exists) {
                $user->password = $password;
            }
            $user->save();

            // Kunci lewat user_id, bukan NIDN baru -- kalau user ini sudah
            // punya baris Lecturer (mis. akun dosen demo bawaan yang
            // namanya sama persis dengan salah satu dosen di daftar ini),
            // baris itu dipakai ulang sebagai SATU identitas, bukan
            // ditambah baris kedua yang bikin dosen kelihatan dobel.
            $lecturer = Lecturer::where('user_id', $user->id)->first()
                // Placeholder unik per baris -- ganti lewat panel admin
                // kalau sudah dapat NIDN asli.
                ?? new Lecturer(['nidn' => '07'.str_pad((string) ($index + 1), 8, '0', STR_PAD_LEFT)]);

            $lecturer->fill([
                'user_id' => $user->id,
                'lecturer_name' => $name,
                'contact' => $email,
                'study_program' => $program,
            ])->save();

            $rows[] = [$program, $role === 'dpm' ? 'Pembimbing' : 'Penguji', $name, $email];
        }

        $this->command?->info('Dosen pembimbing & penguji siap: '.count(self::DOSEN).' dosen ('.(count(self::DOSEN) / 3).' prodi x 3).');
        $this->command?->table(['Program Studi', 'Peran', 'Nama', 'Email'], $rows);
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
