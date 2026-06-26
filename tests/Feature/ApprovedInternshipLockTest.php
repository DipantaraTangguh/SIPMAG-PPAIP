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

    public function test_accepted_partner_application_blocks_new_partner_applications(): void
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
            ->assertUnprocessable()
            ->assertJsonValidationErrors('internship_id');

        $this->assertSame([], Storage::disk('local')->allFiles('cv'));
    }

    public function test_accepted_partner_application_blocks_form2_submissions(): void
    {
        [$user, $student] = $this->studentUser('HasApplication');
        $this->actingAs($user);

        Application::create([
            'student_id' => $student->id,
            'internship_id' => $this->internship()->id,
            'status' => 'Accepted',
        ]);

        $this->postJson('/api/form2', $this->validForm2Payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('company_name');
    }

    public function test_approved_form2_blocks_new_partner_applications_and_form2_submissions(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->studentUser('HasApplication');
        $this->actingAs($user);

        Form2Submission::create([
            'student_id' => $student->id,
            'company_name' => 'PT Disetujui',
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
            ->assertUnprocessable()
            ->assertJsonValidationErrors('internship_id');

        $this->postJson('/api/form2', $this->validForm2Payload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('company_name');

        $this->assertSame([], Storage::disk('local')->allFiles('cv'));
        $this->assertSame(1, $student->form2Submissions()->count());
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
            'alamat_perusahaan' => 'Jl. Rasuna Said No. 1',
            'lingkup_magang' => 'Pengembangan aplikasi internal',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-09-30',
        ];
    }
}
