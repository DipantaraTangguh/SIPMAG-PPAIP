<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DpmAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_kaprodi_can_assign_same_program_dpm(): void
    {
        [$kaprodiUser, $student] = $this->createAssignmentContext();
        $dpm = $this->createLecturer('dpm', 'Sistem Informasi', 'DPM SI');

        $this->actingAs($kaprodiUser);

        $this->postJson('/api/kaprodi/assign-dpm', [
            'student_id' => $student->id,
            'lecturer_id' => $dpm->id,
        ])->assertOk();

        $student->refresh();
        $this->assertSame($dpm->id, $student->dpm_id);
        $this->assertSame('HasDPM', $student->access_status);
    }

    public function test_kaprodi_cannot_assign_a_non_dpm_lecturer(): void
    {
        [$kaprodiUser, $student] = $this->createAssignmentContext();
        $nonDpm = $this->createLecturer('kaprodi', 'Sistem Informasi', 'Bukan DPM');

        $this->actingAs($kaprodiUser);

        $this->postJson('/api/kaprodi/assign-dpm', [
            'student_id' => $student->id,
            'lecturer_id' => $nonDpm->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('lecturer_id');

        $this->assertNull($student->fresh()->dpm_id);
    }

    public function test_kaprodi_cannot_assign_a_dpm_from_another_study_program(): void
    {
        [$kaprodiUser, $student] = $this->createAssignmentContext();
        $otherProgramDpm = $this->createLecturer('dpm', 'Informatika', 'DPM Informatika');

        $this->actingAs($kaprodiUser);

        $this->postJson('/api/kaprodi/assign-dpm', [
            'student_id' => $student->id,
            'lecturer_id' => $otherProgramDpm->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('lecturer_id');

        $this->assertNull($student->fresh()->dpm_id);
    }

    /**
     * @return array{User, Student}
     */
    private function createAssignmentContext(): array
    {
        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => 'KAPRODI-'.fake()->unique()->numerify('#####'),
            'lecturer_name' => 'Kaprodi Sistem Informasi',
            'study_program' => 'Sistem Informasi',
        ]);

        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Uji',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->access_status = 'HasApplication';
        $student->save();

        SupervisorApplication::create([
            'student_id' => $student->id,
            'company_name' => 'PT Contoh',
            'company_contact' => 'Kontak Perusahaan',
            'loa_path' => 'loa/contoh.pdf',
        ]);

        return [$kaprodiUser, $student];
    }

    private function createLecturer(string $role, string $studyProgram, string $name): Lecturer
    {
        $user = User::factory()->create(['role' => $role]);

        return Lecturer::create([
            'user_id' => $user->id,
            'nidn' => strtoupper($role).'-'.fake()->unique()->numerify('#####'),
            'lecturer_name' => $name,
            'study_program' => $studyProgram,
        ]);
    }
}
