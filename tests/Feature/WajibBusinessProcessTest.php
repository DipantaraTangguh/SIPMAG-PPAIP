<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use App\Services\DefenseAssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji proses bisnis magang dari ujung ke ujung: satu mahasiswa berjalan lewat
 * endpoint asli, dari Form 1 sampai siklus tertutup dan direset, dengan semua
 * peran (Kaprodi, DPM, penguji) ikut bermain di tahapnya masing-masing.
 *
 * Tes lain menguji satu tahap dengan status yang dipaksa; yang ini memastikan
 * tahap-tahap itu benar-benar tersambung, termasuk siklus kedua setelah reset.
 */
class WajibBusinessProcessTest extends TestCase
{
    use RefreshDatabase;

    private const PROGRAM = 'Sistem Informasi';

    public function test_wajib_cycle_runs_from_form1_to_completion_then_resets(): void
    {
        Storage::fake('local');

        [$studentUser, $student] = $this->student();

        $this->runWajibCycle($studentUser, $student, $this->internship('PT Mitra Magang'));

        $student->refresh();
        $this->assertSame('CycleCompleted', $student->access_status);
        $this->assertNull($student->dpm_id);

        // Riwayat siklus terbit dan bisa dibaca mahasiswa.
        $cycle = $student->internshipCycles()->first();
        $this->assertSame(1, $cycle->cycle_number);
        $this->assertSame('wajib', $cycle->jenis_magang);
        // DPM 90, rata-rata penguji 82, bobot 1 : 2 -> (90 + 82*2) / 3.
        $this->assertSame(84.67, $cycle->final_score);
        $this->assertSame('A-', $cycle->letter_grade);

        $this->asStudent($studentUser)->getJson('/api/student/cycle/history')
            ->assertOk()
            ->assertJsonCount(1, 'cycles');

        // Mahasiswa mereset siklus: riwayat tetap, state kembali ke awal.
        $this->asStudent($studentUser)->postJson('/api/student/cycle/reset')
            ->assertOk()
            ->assertJsonPath('access_status', 'Unverified');

        $student->refresh();
        $this->assertSame('Unverified', $student->access_status);
        $this->assertNull($student->form1_data);
        $this->assertSame(1, $student->internshipCycles()->count());
        $this->assertSame(0, $student->logbooks()->count()); // diarsipkan

        // Magang wajib hanya sekali; siklus berikutnya harus non-wajib.
        $this->asStudent($studentUser)->postJson('/api/form1', [
            'jenisMagang' => 'wajib',
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'Magang wajib kedua',
            'outputTarget' => 'Laporan',
        ])->assertStatus(422);

        $this->asStudent($studentUser)->postJson('/api/form1', [
            'jenisMagang' => 'non_wajib',
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'Magang tambahan',
            'outputTarget' => 'Laporan',
        ])->assertCreated();
    }

    /**
     * Siklus kedua harus benar-benar bisa dijalani sampai habis: record siklus
     * pertama hanya di-soft-delete, jadi indeks unik per mahasiswa tidak boleh
     * ikut mengunci siklus berikutnya.
     */
    public function test_second_cycle_runs_to_completion_after_a_non_wajib_cycle(): void
    {
        Storage::fake('local');

        [$studentUser, $student] = $this->student();
        [$kaprodiUser] = $this->lecturer('kaprodi', 'Kaprodi SI');
        $internship = $this->internship('PT Mitra Pertama');

        // -- Siklus 1: non-wajib lewat lowongan mitra, selesai di konfirmasi --
        $this->asStudent($studentUser)->postJson('/api/form1', [
            'jenisMagang' => 'non_wajib',
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'Magang non-wajib pertama',
            'outputTarget' => 'Laporan',
        ])->assertCreated();

        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk();

        $this->applyToInternship($studentUser, $internship);

        $this->asStudent($studentUser)->post('/api/student/cycle/confirm', [
            'hasil' => 'diterima',
            'company_name' => 'PT Mitra Pertama',
            'tanggal_mulai' => '2026-02',
            'tanggal_selesai' => '2026-04',
            'loa_file' => UploadedFile::fake()->create('loa.pdf', 100, 'application/pdf'),
        ])->assertOk()->assertJsonPath('access_status', 'ElectiveCompleted');

        $this->asStudent($studentUser)->postJson('/api/student/cycle/reset')->assertOk();

        // -- Siklus 2: wajib, jalur penuh sampai sidang dinilai ---------------
        $this->runWajibCycle($studentUser, $student, $this->internship('PT Mitra Kedua'));

        $student->refresh();
        $this->assertSame('CycleCompleted', $student->access_status);

        $cycles = $student->internshipCycles()->orderBy('cycle_number')->get();
        $this->assertSame([1, 2], $cycles->pluck('cycle_number')->all());
        $this->assertSame(['non_wajib', 'wajib'], $cycles->pluck('jenis_magang')->all());
    }

    /** Tahap tidak boleh dilompati: sidang sebelum logbook lengkap ditolak. */
    public function test_defense_is_refused_before_logbooks_are_complete(): void
    {
        Storage::fake('local');

        [$studentUser, $student] = $this->student();
        $student->forceFill(['access_status' => 'HasDPM'])->save();

        $this->submitDefense($studentUser)->assertForbidden();

        $this->assertNull($student->fresh()->sidangSubmission);
    }

    /**
     * Jalur wajib penuh: Form 1 -> Kaprodi -> lamaran mitra -> pengajuan DPM
     * -> 6 logbook -> sidang -> jadwal -> tiga penilaian.
     */
    private function runWajibCycle(User $studentUser, Student $student, Internship $internship): void
    {
        [$kaprodiUser] = $this->lecturer('kaprodi', 'Kaprodi Sidang');
        [$dpmUser, $dpm] = $this->lecturer('dpm', 'Dosen Pembimbing');
        [$examinerOneUser, $examinerOne] = $this->lecturer('dosen_penguji', 'Penguji Satu');
        [$examinerTwoUser, $examinerTwo] = $this->lecturer('dosen_penguji', 'Penguji Dua');

        // 1. Mahasiswa mengajukan Form 1 magang wajib.
        $this->asStudent($studentUser)->postJson('/api/form1', [
            'jenisMagang' => 'wajib',
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'Pengembangan sistem informasi magang',
            'outputTarget' => 'Laporan',
        ])->assertCreated()->assertJsonPath('access_status', 'PendingReview');

        // 2. Kaprodi prodi terkait menyetujui.
        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk();

        $this->assertSame('ApprovedForm1', $student->fresh()->access_status);

        // 3. Mahasiswa melamar lowongan mitra.
        $this->applyToInternship($studentUser, $internship);

        // 4. Mahasiswa mengajukan pembimbing beserta LoA. Periode magang
        //    dimulai di masa lalu supaya logbook harian bisa diisi.
        $mulai = today()->subDays(30);

        $this->asStudent($studentUser)->post('/api/supervisor-application', [
            'company_name' => $internship->company_name,
            'company_contact' => 'Budi - 08123456789',
            'lingkup_magang' => 'Membangun API internal.',
            'nama_praktisi' => 'Budi Praktisi',
            'jabatan_praktisi' => 'Engineering Manager',
            'no_telepon' => '081234567890',
            'email' => 'praktisi@example.test',
            'mulai_magang' => $mulai->toDateString(),
            'selesai_magang' => today()->addDays(60)->toDateString(),
            'loa_file' => UploadedFile::fake()->create('loa.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        // 5. Kaprodi menunjuk DPM.
        $this->actingAs($kaprodiUser)->postJson('/api/kaprodi/assign-dpm', [
            'student_id' => $student->id,
            'lecturer_id' => $dpm->id,
        ])->assertOk();

        $this->assertSame('HasDPM', $student->fresh()->access_status);

        // 6. Enam logbook diisi mahasiswa lalu disetujui DPM. Status baru naik
        //    pada persetujuan keenam.
        for ($i = 0; $i < 6; $i++) {
            $this->asStudent($studentUser)->postJson('/api/logbooks', [
                'tanggal' => $mulai->copy()->addDays($i)->toDateString(),
                'kegiatan_harian' => 'Mengerjakan modul '.($i + 1),
                'hasil' => 'Modul '.($i + 1).' selesai',
            ])->assertCreated();
        }

        $logbookIds = $student->logbooks()->orderBy('id')->pluck('id');

        foreach ($logbookIds as $index => $logbookId) {
            $this->actingAs($dpmUser)
                ->postJson("/api/dpm/logbooks/{$logbookId}/approve")
                ->assertOk();

            $expected = $index < 5 ? 'HasDPM' : 'LogbookComplete';
            $this->assertSame($expected, $student->fresh()->access_status);
        }

        // 7. Mahasiswa mendaftar sidang.
        $this->submitDefense($studentUser)
            ->assertCreated()
            ->assertJsonPath('access_status', 'AwaitingDefense');

        // 8. Kaprodi menjadwalkan sidang dengan dua penguji.
        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/defense/{$student->id}/schedule", [
                'scheduled_date' => today()->addWeek()->toDateString(),
                'scheduled_time' => '09:00',
                'room' => 'Ruang 301',
                'dosen_penguji_1_id' => $examinerOne->id,
                'dosen_penguji_2_id' => $examinerTwo->id,
            ])->assertOk();

        $submission = $student->fresh()->sidangSubmission;
        $this->assertSame('Scheduled', $submission->status);

        // 9. Tiga penilai mengisi nilai; penilai terakhir menutup siklus.
        $assessments = app(DefenseAssessmentService::class);
        $assessments->save($dpmUser, $submission, $this->scores(90));
        $assessments->save($examinerOneUser, $submission, $this->scores(80));

        $this->assertSame('AwaitingDefense', $student->fresh()->access_status);

        $assessments->save($examinerTwoUser, $submission, $this->scores(84));
    }

    private function applyToInternship(User $studentUser, Internship $internship): void
    {
        $this->asStudent($studentUser)->post('/api/applications', [
            'internship_id' => $internship->id,
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertCreated();
    }

    private function submitDefense(User $studentUser): \Illuminate\Testing\TestResponse
    {
        return $this->asStudent($studentUser)->post('/api/defense', [
            'laporan' => UploadedFile::fake()->create('laporan.pdf', 200, 'application/pdf'),
            'poster' => UploadedFile::fake()->create('poster.pdf', 100, 'application/pdf'),
            'foto_kegiatan_1' => UploadedFile::fake()->create('foto-1.pdf', 50, 'application/pdf'),
            'foto_kegiatan_2' => UploadedFile::fake()->create('foto-2.pdf', 50, 'application/pdf'),
        ]);
    }

    private function internship(string $company): Internship
    {
        return Internship::create([
            'company_name' => $company,
            'position' => 'Backend Developer',
            'description' => 'Magang backend.',
            'capacity' => 5,
            'duration' => '4 bulan',
            'study_programs' => [self::PROGRAM],
            'location' => 'Jakarta',
            'deadline' => today()->addMonth()->toDateString(),
            'is_active' => true,
        ]);
    }

    /** @return array{User, Student} */
    private function student(): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Proses Bisnis',
            'study_program' => self::PROGRAM,
            'email' => fake()->unique()->safeEmail(),
            'semester' => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks' => 120,
            'ipk' => 3.50,
            'access_status' => 'Unverified',
        ]);

        return [$user, $student];
    }

    /** @return array{User, Lecturer} */
    private function lecturer(string $role, string $name): array
    {
        $user = User::factory()->create(['name' => $name, 'role' => $role]);

        $lecturer = Lecturer::create([
            'user_id' => $user->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => $name,
            'contact' => $user->email,
            'study_program' => self::PROGRAM,
        ]);

        return [$user, $lecturer];
    }

    /** Relasi student ikut ter-cache di instance user, jadi selalu ambil segar. */
    private function asStudent(User $user): static
    {
        return $this->actingAs($user->fresh());
    }

    /** @return array<string, float> */
    private function scores(float $score): array
    {
        return [
            'internship_performance_score' => $score,
            'final_report_score' => $score,
            'presentation_score' => $score,
        ];
    }
}
