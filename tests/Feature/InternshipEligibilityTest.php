<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternshipEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_returns_only_active_non_expired_vacancies(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($user);

        $open = $this->createInternship(true, today()->addDay());
        $this->createInternship(false, today()->addDay());
        $this->createInternship(true, today()->subDay());

        $response = $this->getJson('/api/internships')->assertOk();

        $response->assertJsonCount(1, 'internships.data');
        $response->assertJsonPath('internships.data.0.id', $open->id);
    }

    public function test_inactive_or_expired_vacancy_detail_is_not_exposed(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($user);

        $inactive = $this->createInternship(false, today()->addDay());
        $expired = $this->createInternship(true, today()->subDay());

        $this->getJson("/api/internships/{$inactive->id}")->assertNotFound();
        $this->getJson("/api/internships/{$expired->id}")->assertNotFound();
    }

    public function test_student_cannot_apply_to_inactive_or_expired_vacancy(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        $this->actingAs($user);

        foreach ([
            $this->createInternship(false, today()->addDay()),
            $this->createInternship(true, today()->subDay()),
        ] as $internship) {
            $this->withHeader('Accept', 'application/json')->post('/api/applications', [
                'internship_id' => $internship->id,
                'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('internship_id');
        }

        $this->assertCount(0, $student->applications()->get());
        $this->assertSame([], Storage::disk('local')->allFiles('cv'));
    }

    public function test_student_can_apply_on_the_deadline_date(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        $internship = $this->createInternship(true, today());
        $this->actingAs($user);

        $this->post('/api/applications', [
            'internship_id' => $internship->id,
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        $application = $student->applications()->firstOrFail();
        $this->assertSame($internship->id, $application->internship_id);
        $this->assertSame('HasApplication', $student->fresh()->access_status);
        $this->assertTrue(Storage::disk('local')->exists($application->cv_file_path));
    }

    public function test_student_cannot_apply_to_the_same_internship_twice(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        $internship = $this->createInternship(true, today()->addDay());
        $this->actingAs($user);

        $this->post('/api/applications', [
            'internship_id' => $internship->id,
            'cv_file' => UploadedFile::fake()->create('cv-1.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        $this->withHeader('Accept', 'application/json')->post('/api/applications', [
            'internship_id' => $internship->id,
            'cv_file' => UploadedFile::fake()->create('cv-2.pdf', 100, 'application/pdf'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('internship_id');

        $this->assertSame(1, $student->applications()->count());
        $this->assertCount(1, Storage::disk('local')->allFiles('cv'));
    }

    /**
     * @return array{User, Student}
     */
    private function createEligibleStudent(): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Pelamar',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->access_status = 'ApprovedForm1';
        $student->save();

        return [$user, $student];
    }

    private function createInternship(bool $isActive, Carbon $deadline): Internship
    {
        return Internship::create([
            'company_name' => fake()->unique()->company(),
            'position' => 'Software Engineer Intern',
            'description' => 'Deskripsi lowongan.',
            'deadline' => $deadline,
            'is_active' => $isActive,
        ]);
    }
}
