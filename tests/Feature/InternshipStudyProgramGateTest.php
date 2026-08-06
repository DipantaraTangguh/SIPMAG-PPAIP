<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InternshipStudyProgramGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_from_a_listed_study_program_can_apply(): void
    {
        [$user, $student] = $this->student('Sistem Informasi');
        $internship = $this->internship(['Informatika', 'Sistem Informasi']);

        $this->actingAs($user)
            ->post('/api/applications', [
                'internship_id' => $internship->id,
                'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ])
            ->assertCreated();

        $this->assertSame(1, Application::where('student_id', $student->id)->count());
    }

    public function test_student_from_another_study_program_is_rejected(): void
    {
        [$user, $student] = $this->student('Akuntansi');
        $internship = $this->internship(['Informatika', 'Sistem Informasi']);

        $this->actingAs($user)
            ->postJson('/api/applications', [
                'internship_id' => $internship->id,
                'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('internship_id');

        $this->assertSame(0, Application::where('student_id', $student->id)->count());
        // Status tidak boleh ikut maju kalau lamarannya ditolak.
        $this->assertSame('ApprovedForm1', $student->fresh()->access_status);
    }

    public function test_vacancy_without_study_programs_stays_open_to_everyone(): void
    {
        [$user, $student] = $this->student('Teknik Sipil');
        $internship = $this->internship(null);

        $this->actingAs($user)
            ->post('/api/applications', [
                'internship_id' => $internship->id,
                'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ])
            ->assertCreated();

        $this->assertSame(1, Application::where('student_id', $student->id)->count());
    }

    public function test_ppaip_can_save_multiple_study_programs_through_the_filament_form(): void
    {
        $ppaipUser = User::factory()->create(['role' => 'ppaip']);

        \Livewire\Livewire::actingAs($ppaipUser)
            ->test(\App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages\CreateInternship::class)
            ->fillForm([
                'company_name' => 'PT Multi Prodi',
                'position' => 'Intern',
                'description' => 'Deskripsi magang',
                'location' => 'Jakarta Selatan',
                'sistem_kerja' => 'Hybrid',
                'study_programs' => ['Teknik Industri', 'Teknik Lingkungan'],
                'job_description' => [['item' => 'Membantu tim produksi']],
                'skills' => [['item' => 'Excel']],
                'requirements' => [['item' => 'Semester 6 ke atas']],
                'deadline' => today()->addMonth()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $internship = Internship::where('company_name', 'PT Multi Prodi')->firstOrFail();
        $this->assertSame(['Teknik Industri', 'Teknik Lingkungan'], $internship->study_programs);
    }

    /**
     * @return array{User, Student}
     */
    private function student(string $studyProgram): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa '.$studyProgram,
            'study_program' => $studyProgram,
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->forceFill(['access_status' => 'ApprovedForm1'])->save();

        return [$user, $student];
    }

    /**
     * @param  array<int, string>|null  $studyPrograms
     */
    private function internship(?array $studyPrograms): Internship
    {
        return Internship::create([
            'company_name' => 'PT Contoh',
            'position' => 'Intern',
            'description' => 'Deskripsi magang',
            'study_programs' => $studyPrograms,
            'deadline' => today()->addMonth(),
            'is_active' => true,
        ]);
    }
}
