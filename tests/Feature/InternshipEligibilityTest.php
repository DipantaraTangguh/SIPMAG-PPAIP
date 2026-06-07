<?php

namespace Tests\Feature;

use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InternshipEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_returns_only_active_non_expired_vacancies(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        Sanctum::actingAs($user);

        $open = $this->createInternship(true, today()->addDay());
        $this->createInternship(false, today()->addDay());
        $this->createInternship(true, today()->subDay());

        $response = $this->getJson('/api/internships')->assertOk();

        $response->assertJsonCount(1, 'internships');
        $response->assertJsonPath('internships.0.id', $open->id);
    }

    public function test_inactive_or_expired_vacancy_detail_is_not_exposed(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        Sanctum::actingAs($user);

        $inactive = $this->createInternship(false, today()->addDay());
        $expired = $this->createInternship(true, today()->subDay());

        $this->getJson("/api/internships/{$inactive->id}")->assertNotFound();
        $this->getJson("/api/internships/{$expired->id}")->assertNotFound();
    }

    public function test_student_cannot_apply_to_inactive_or_expired_vacancy(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        Sanctum::actingAs($user);

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
        Sanctum::actingAs($user);

        $this->post('/api/applications', [
            'internship_id' => $internship->id,
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        $application = $student->applications()->firstOrFail();
        $this->assertSame($internship->id, $application->internship_id);
        $this->assertSame('HasApplication', $student->fresh()->access_status);
        $this->assertTrue(Storage::disk('local')->exists($application->cv_file_path));
    }

    private function createEligibleStudent(): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Pelamar',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
            'access_status' => 'ApprovedForm1',
        ]);

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
