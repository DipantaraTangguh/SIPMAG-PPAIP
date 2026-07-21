<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Form2Submission;
use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApprovedInternshipLockTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepted_partner_application_allows_new_partner_applications(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->studentUser('HasApplication');
        $this->actingAs($user);

        Application::create([
            'student_id' => $student->id,
            'internship_id' => $this->internship()->id,
            'status' => 'Accepted',
        ]);

        $this->withHeader('Accept', 'application/json')->post('/api/applications', [
            'internship_id' => $this->internship()->id,
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])
            ->assertCreated();

        $this->assertSame(2, $student->applications()->count());
        $this->assertCount(1, Storage::disk('local')->allFiles('cv'));
    }

    public function test_accepted_partner_application_allows_form2_submissions(): void
    {
        [$user, $student] = $this->studentUser('HasApplication');
        $this->actingAs($user);

        Application::create([
            'student_id' => $student->id,
            'internship_id' => $this->internship()->id,
            'status' => 'Accepted',
        ]);

        $this->postJson('/api/form2', $this->validForm2Payload())
            ->assertCreated()
            ->assertJsonPath('submission.status', 'PendingReview');
    }

    public function test_approved_form2_allows_new_partner_applications_and_more_form2_submissions(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->studentUser('HasApplication');
        $this->actingAs($user);

        Form2Submission::create([
            'student_id' => $student->id,
            'company_name' => 'PT Disetujui',
            'nama_pimpinan' => 'Direktur Disetujui',
            'jabatan_pimpinan' => 'Direktur',
            'alamat_perusahaan' => 'Jl. Disetujui No. 1',
            'lingkup_magang' => 'Pengembangan aplikasi',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-09-30',
            'status' => 'ApprovedForm2',
        ]);

        $this->withHeader('Accept', 'application/json')->post('/api/applications', [
            'internship_id' => $this->internship()->id,
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])
            ->assertCreated();

        $this->postJson('/api/form2', $this->validForm2Payload())
            ->assertCreated()
            ->assertJsonPath('submission.company_name', 'PT Contoh Indonesia')
            ->assertJsonPath('submission.status', 'PendingReview');

        $this->assertCount(1, Storage::disk('local')->allFiles('cv'));
        $this->assertSame(2, $student->form2Submissions()->count());
        $this->assertSame(1, $student->applications()->count());
    }

    public function test_dpm_assignment_blocks_new_partner_applications_and_form2_submissions(): void
    {
        Storage::fake('local');

        [$user] = $this->studentUser('HasDPM');
        $this->actingAs($user);

        $this->withHeader('Accept', 'application/json')->post('/api/applications', [
            'internship_id' => $this->internship()->id,
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('internship_id');

        $this->postJson('/api/form2', $this->validForm2Payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('company_name');

        $this->assertSame([], Storage::disk('local')->allFiles('cv'));
    }

    public function test_menunggu_konfirmasi_allows_new_form2_submissions(): void
    {
        [$user, $student] = $this->studentUser('MenungguKonfirmasi');
        $this->actingAs($user);

        // Mahasiswa non-wajib yang sudah punya Form 2 approved tetap boleh submit lagi.
        Form2Submission::create([
            'student_id' => $student->id,
            'company_name' => 'PT Sudah Disetujui',
            'nama_pimpinan' => 'Direktur',
            'jabatan_pimpinan' => 'Direktur Utama',
            'alamat_perusahaan' => 'Jl. Sudah No. 1',
            'lingkup_magang' => 'Pengembangan',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-09-30',
            'status' => 'ApprovedForm2',
        ]);

        $this->postJson('/api/form2', $this->validForm2Payload())
            ->assertCreated()
            ->assertJsonPath('submission.status', 'PendingReview');

        $this->assertSame(2, $student->form2Submissions()->count());
    }

    /**
     * @return array{User, Student}
     */
    private function studentUser(string $accessStatus): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Terkunci',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->forceFill(['access_status' => $accessStatus])->save();

        return [$user, $student];
    }

    private function internship(): Internship
    {
        return Internship::create([
            'company_name' => fake()->unique()->company(),
            'position' => 'Software Engineer Intern',
            'description' => 'Deskripsi lowongan.',
            'deadline' => Carbon::today()->addMonth(),
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function validForm2Payload(): array
    {
        return [
            'company_name' => 'PT Contoh Indonesia',
            'nama_pimpinan' => 'Budi Santoso',
            'jabatan_pimpinan' => 'Direktur Utama',
            'alamat_perusahaan' => 'Jl. Rasuna Said No. 1',
            'lingkup_magang' => 'Pengembangan aplikasi internal',
            'tanggal_mulai' => '2026-07',
            'tanggal_selesai' => '2026-09',
        ];
    }
}
