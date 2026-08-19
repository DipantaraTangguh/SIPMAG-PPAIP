<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Mengunci titik percabangan alur non-wajib:
 *
 *   Form 1 -> disetujui Kaprodi -> Form 2 ATAU lamar mitra (salah satu)
 *   -> konfirmasi hasil magang.
 *
 * Form 1 yang disetujui TIDAK boleh langsung melompat ke tahap konfirmasi;
 * mahasiswa harus mengamankan tempat lebih dulu lewat salah satu jalur.
 */
class NonWajibBranchAfterForm1Test extends TestCase
{
    use RefreshDatabase;

    public function test_approved_form1_stops_at_approved_form1_for_non_wajib(): void
    {
        [$user, $student] = $this->student('3101214300');

        $this->approveForm1($student, $user, 'non_wajib');

        $this->assertSame('ApprovedForm1', $student->fresh()->access_status);
    }

    public function test_non_wajib_may_take_the_form2_route(): void
    {
        [$user, $student] = $this->student('3101214301');
        $this->approveForm1($student, $user, 'non_wajib');

        $user->refresh();
        $this->actingAs($user)->postJson('/api/form2', [
            'company_name' => 'PT Jalur Form 2',
            'alamat_perusahaan' => 'Jl. Contoh No. 1',
            'lingkup_magang' => 'Pengembangan aplikasi',
            'tanggal_mulai' => '2026-08',
            'tanggal_selesai' => '2026-10',
        ])->assertCreated();

        // PPAIP menyetujui -> baru masuk tahap konfirmasi.
        $ppaip = User::factory()->create(['role' => 'ppaip']);
        $submissionId = $student->form2Submissions()->first()->id;

        $this->actingAs($ppaip)
            ->postJson("/api/ppaip/form2/{$submissionId}/approve")
            ->assertOk();

        $this->assertSame('AwaitingConfirmation', $student->fresh()->access_status);
    }

    public function test_non_wajib_may_take_the_mitra_route_instead(): void
    {
        [$user, $student] = $this->student('3101214302');
        $this->approveForm1($student, $user, 'non_wajib');

        $internship = Internship::create([
            'company_name' => 'PT Jalur Mitra',
            'position' => 'Intern',
            'description' => 'Deskripsi',
            'deadline' => today()->addMonth(),
            'is_active' => true,
        ]);

        $user->refresh();
        $this->actingAs($user)->post('/api/applications', [
            'internship_id' => $internship->id,
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        $this->assertSame('HasApplication', $student->fresh()->access_status);
    }

    public function test_wajib_also_stops_at_approved_form1(): void
    {
        [$user, $student] = $this->student('3101214303');

        $this->approveForm1($student, $user, 'wajib');

        $this->assertSame('ApprovedForm1', $student->fresh()->access_status);
    }

    /**
     * @return array{User, Student}
     */
    private function student(string $nim): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => $nim,
            'name' => 'Mahasiswa '.$nim,
            'study_program' => 'Sistem Informasi',
            'email' => $nim.'@example.test',
            'semester' => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks' => 120,
            'ipk' => 3.5,
        ]);

        return [$user, $student];
    }

    private function approveForm1(Student $student, User $user, string $jenis): void
    {
        $this->actingAs($user)->postJson('/api/form1', [
            'jenisMagang' => $jenis,
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'PT Contoh',
            'outputTarget' => 'Laporan',
        ])->assertCreated();

        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodi->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'Kaprodi SI',
            'contact' => $kaprodi->email,
            'study_program' => 'Sistem Informasi',
        ]);

        $this->actingAs($kaprodi)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk()
            ->assertJsonPath('access_status', 'ApprovedForm1');
    }
}
