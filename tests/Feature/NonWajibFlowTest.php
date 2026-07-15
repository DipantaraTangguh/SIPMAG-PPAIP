<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NonWajibFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_wajib_flow_stops_at_form2_and_records_history(): void
    {
        // -- Mahasiswa mengajukan Form 1 non-wajib -----------------------------
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $studentUser->id,
            'nim'            => '1101214250',
            'name'           => 'Mahasiswa Non Wajib',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'non-wajib@example.test',
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 110,
            'ipk'            => 3.40,
            'access_status'  => 'Unverified',
        ]);

        $this->actingAs($studentUser)->post('/api/form1', [
            'jenisMagang'  => 'non_wajib',
            'skemaMagang'  => 'Magang Perusahaan',
            'topikMagang'  => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
        ])->assertCreated();

        // -- Kaprodi menyetujui Form 1 -----------------------------------------
        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => '1234567890',
            'lecturer_name' => 'Kaprodi SI',
            'contact' => $kaprodiUser->email,
            'study_program' => 'Sistem Informasi',
        ]);

        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk();

        // -- Mahasiswa mengajukan Form 2 ----------------------------------------
        // Refresh: relasi student yang di-cache pada instance user sudah stale.
        $studentUser->refresh();
        $this->actingAs($studentUser)->postJson('/api/form2', [
            'company_name'      => 'PT Contoh Indonesia',
            'nama_pimpinan'     => 'Budi Pimpinan',
            'jabatan_pimpinan'  => 'HRD Manager',
            'alamat_perusahaan' => 'Jl. Contoh No. 1, Jakarta',
            'lingkup_magang'    => 'Pengembangan aplikasi web',
            'tanggal_mulai'     => '2026-08',
            'tanggal_selesai'   => '2026-10',
        ])->assertCreated();

        $submissionId = $student->form2Submissions()->first()->id;

        // -- PPAIP menyetujui Form 2: non-wajib langsung selesai ---------------
        $ppaipUser = User::factory()->create(['role' => 'ppaip']);

        $this->actingAs($ppaipUser)
            ->postJson("/api/ppaip/form2/{$submissionId}/approve")
            ->assertOk();

        $student->refresh();

        // Surat pengantar bukan bukti diterima: masuk tahap konfirmasi dulu.
        $this->assertSame('MenungguKonfirmasi', $student->access_status);
        $this->assertSame(0, $student->internshipCycles()->count());

        // Form 2 terkunci selama menunggu konfirmasi.
        $studentUser->refresh();
        $this->actingAs($studentUser)->postJson('/api/form2', [
            'company_name'      => 'PT Lain',
            'alamat_perusahaan' => 'Jl. Lain No. 2',
            'lingkup_magang'    => 'Magang lagi',
            'tanggal_mulai'     => '2026-11',
            'tanggal_selesai'   => '2026-12',
        ])->assertUnprocessable();

        // Mahasiswa konfirmasi diterima + upload LoA; tempat/periode aktual
        // (boleh berbeda dari Form 2) yang masuk riwayat.
        Storage::fake('local');
        $this->actingAs($studentUser)->post('/api/student/cycle/confirm', [
            'hasil'             => 'diterima',
            'company_name'      => 'PT Aktual Diterima',
            'alamat_perusahaan' => 'Jl. Aktual No. 9',
            'tanggal_mulai'     => '2026-09',
            'tanggal_selesai'   => '2026-11',
            'loa_file'          => UploadedFile::fake()->create('loa.pdf', 100, 'application/pdf'),
        ])->assertOk()->assertJsonPath('access_status', 'SelesaiNonWajib');

        $student->refresh();

        // Selesai tanpa masuk jalur DPM.
        $this->assertSame('SelesaiNonWajib', $student->access_status);
        $this->assertNull($student->dpm_id);
        $this->assertSame(0, $student->logbooks()->count());

        // Riwayat memakai data KONFIRMASI, bukan Form 2, lengkap dengan LoA.
        $cycle = $student->internshipCycles()->first();
        $this->assertNotNull($cycle);
        $this->assertSame(1, $cycle->cycle_number);
        $this->assertSame('non_wajib', $cycle->jenis_magang);
        $this->assertSame('SelesaiNonWajib', $cycle->outcome_status);
        $this->assertSame('PT Aktual Diterima', $cycle->company_name);
        $this->assertSame('2026-09-01', $cycle->tanggal_mulai->toDateString());
        $this->assertSame('2026-11-01', $cycle->tanggal_selesai->toDateString());
        $this->assertNotNull($cycle->loa_path);
        $this->assertNull($cycle->final_score);
    }

    public function test_non_wajib_rejected_by_company_can_resubmit_form2(): void
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $studentUser->id,
            'nim'            => '1101214254',
            'name'           => 'Mahasiswa Ditolak Perusahaan',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'ditolak-perusahaan@example.test',
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 110,
            'ipk'            => 3.40,
        ]);
        $student->forceFill([
            'access_status' => 'MenungguKonfirmasi',
            'form1_data' => ['jenisMagang' => 'non_wajib', 'skemaMagang' => 'Magang Perusahaan'],
        ])->save();

        // Ditolak perusahaan → mundur, tidak ada riwayat tercatat.
        $this->actingAs($studentUser)->postJson('/api/student/cycle/confirm', [
            'hasil' => 'ditolak',
        ])->assertOk()->assertJsonPath('access_status', 'ApprovedForm1');

        $student->refresh();
        $this->assertSame('ApprovedForm1', $student->access_status);
        $this->assertSame(0, $student->internshipCycles()->count());

        // Boleh mengajukan Form 2 lagi ke perusahaan lain.
        $studentUser->refresh();
        $this->actingAs($studentUser)->postJson('/api/form2', [
            'company_name'      => 'PT Kesempatan Kedua',
            'alamat_perusahaan' => 'Jl. Baru No. 2',
            'lingkup_magang'    => 'Magang lagi',
            'tanggal_mulai'     => '2026-11',
            'tanggal_selesai'   => '2026-12',
        ])->assertCreated();
    }

    public function test_non_wajib_mitra_application_acceptance_completes_the_cycle(): void
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $studentUser->id,
            'nim'            => '1101214252',
            'name'           => 'Mahasiswa Non Wajib Mitra',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'non-wajib-mitra@example.test',
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 110,
            'ipk'            => 3.40,
        ]);
        $student->forceFill([
            'access_status' => 'HasApplication',
            'form1_data' => ['jenisMagang' => 'non_wajib', 'skemaMagang' => 'Magang Perusahaan'],
        ])->save();

        $internship = Internship::create([
            'company_name' => 'PT Mitra PPAIP',
            'position' => 'Software Engineer Intern',
            'description' => 'Magang pengembangan aplikasi.',
            'location' => 'Jakarta Selatan',
            'start_date' => '2026-09-01',
            'deadline' => now()->addMonth()->toDateString(),
            'is_active' => true,
        ]);

        $application = Application::create([
            'student_id' => $student->id,
            'internship_id' => $internship->id,
            'cv_file_path' => 'cv/tes.pdf',
            'status' => 'Applied',
        ]);

        // PPAIP menerima lamaran → mahasiswa non-wajib tetap wajib konfirmasi LoA.
        $application->update(['status' => 'Accepted']);

        $student->refresh();

        $this->assertSame('MenungguKonfirmasi', $student->access_status);
        $this->assertSame(0, $student->internshipCycles()->count());

        // Tahap DPM tertutup untuk magang non-wajib.
        $studentUser->refresh();
        $this->actingAs($studentUser)
            ->postJson('/api/supervisor-application', $this->supervisorPayload())
            ->assertForbidden();

        // Konfirmasi diterima + LoA → selesai + tercatat di riwayat.
        Storage::fake('local');
        $this->actingAs($studentUser)->post('/api/student/cycle/confirm', [
            'hasil'           => 'diterima',
            'company_name'    => 'PT Mitra PPAIP',
            'alamat_perusahaan' => 'Jakarta Selatan',
            'tanggal_mulai'   => '2026-09',
            'tanggal_selesai' => '2026-12',
            'loa_file'        => UploadedFile::fake()->create('loa.pdf', 100, 'application/pdf'),
        ])->assertOk()->assertJsonPath('access_status', 'SelesaiNonWajib');

        $student->refresh();
        $this->assertSame('SelesaiNonWajib', $student->access_status);

        $cycle = $student->internshipCycles()->first();
        $this->assertNotNull($cycle);
        $this->assertSame('non_wajib', $cycle->jenis_magang);
        $this->assertSame('PT Mitra PPAIP', $cycle->company_name);
        $this->assertSame('Jakarta Selatan', $cycle->alamat_perusahaan);
        $this->assertNotNull($cycle->loa_path);
    }

    public function test_non_wajib_student_cannot_request_dpm_while_application_pending(): void
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $studentUser->id,
            'nim'            => '1101214253',
            'name'           => 'Mahasiswa Non Wajib DPM',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'non-wajib-dpm@example.test',
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 110,
            'ipk'            => 3.40,
        ]);
        $student->forceFill([
            'access_status' => 'HasApplication',
            'form1_data' => ['jenisMagang' => 'non_wajib', 'skemaMagang' => 'Magang Perusahaan'],
        ])->save();

        $this->actingAs($studentUser)
            ->postJson('/api/supervisor-application', $this->supervisorPayload())
            ->assertForbidden()
            ->assertJsonPath('message', 'Magang non-wajib tidak melanjutkan ke tahap DPM dan sidang.');
    }

    /** Payload valid agar lolos FormRequest dan sampai ke guard jenis magang. */
    private function supervisorPayload(): array
    {
        Storage::fake('local');

        return [
            'company_name' => 'PT Contoh',
            'company_contact' => 'Budi - 08123456789',
            'nama_praktisi' => 'Budi Praktisi',
            'jabatan_praktisi' => 'Manager',
            'no_telepon' => '081234567890',
            'email' => 'praktisi@example.test',
            'mulai_magang' => '2026-08-01',
            'selesai_magang' => '2026-12-01',
            'loa_file' => UploadedFile::fake()->create('loa.pdf', 100, 'application/pdf'),
        ];
    }

    public function test_wajib_flow_still_proceeds_to_dpm_stage_after_form2(): void
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $studentUser->id,
            'nim'            => '1101214251',
            'name'           => 'Mahasiswa Wajib',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'wajib@example.test',
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 110,
            'ipk'            => 3.40,
        ]);
        $student->forceFill([
            'access_status' => 'ApprovedForm1',
            'form1_data' => ['jenisMagang' => 'wajib', 'skemaMagang' => 'Magang Perusahaan'],
        ])->save();

        $this->actingAs($studentUser)->postJson('/api/form2', [
            'company_name'      => 'PT Wajib Jaya',
            'alamat_perusahaan' => 'Jl. Wajib No. 1',
            'lingkup_magang'    => 'Magang wajib',
            'tanggal_mulai'     => '2026-08',
            'tanggal_selesai'   => '2026-12',
        ])->assertCreated();

        $submissionId = $student->form2Submissions()->first()->id;
        $ppaipUser = User::factory()->create(['role' => 'ppaip']);

        $this->actingAs($ppaipUser)
            ->postJson("/api/ppaip/form2/{$submissionId}/approve")
            ->assertOk();

        $student->refresh();

        // Wajib lanjut ke jalur DPM, belum tercatat di riwayat.
        $this->assertSame('HasApplication', $student->access_status);
        $this->assertSame(0, $student->internshipCycles()->count());
    }
}
