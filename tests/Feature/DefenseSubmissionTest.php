<?php

namespace Tests\Feature;

use App\Models\DefenseSubmission;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DefenseSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_defense_submission_requires_both_activity_photos(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        $this->actingAs($user);

        $this->postJson('/api/defense', [
            'laporan' => $this->pdf('laporan.pdf'),
            'poster' => $this->pdf('poster.pdf'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'foto_kegiatan_1',
                'foto_kegiatan_2',
            ]);

        $this->assertNull($student->sidangSubmission()->first());
        $this->assertSame([], Storage::disk('local')->allFiles('sidang'));
    }

    public function test_defense_submission_persists_all_four_documents(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        $this->actingAs($user);

        $this->post('/api/defense', [
            'laporan' => $this->pdf('laporan.pdf'),
            'poster' => $this->pdf('poster.pdf'),
            'foto_kegiatan_1' => $this->pdf('foto-kegiatan-1.pdf'),
            'foto_kegiatan_2' => $this->pdf('foto-kegiatan-2.pdf'),
        ])->assertCreated();

        $submission = $student->sidangSubmission()->firstOrFail();
        $paths = [
            $submission->laporan_path,
            $submission->poster_path,
            $submission->foto_kegiatan_1_path,
            $submission->foto_kegiatan_2_path,
        ];

        $this->assertCount(4, array_filter($paths));
        $this->assertCount(4, array_unique($paths));

        foreach ($paths as $path) {
            $this->assertTrue(Storage::disk('local')->exists($path));
        }

        $this->assertSame('AwaitingDefense', $student->fresh()->access_status);

        $this->getJson('/api/defense')
            ->assertOk()
            ->assertJsonPath('submission.foto_kegiatan_1_path', $submission->foto_kegiatan_1_path)
            ->assertJsonPath('submission.foto_kegiatan_2_path', $submission->foto_kegiatan_2_path);
    }

    public function test_kaprodi_schedules_defense_with_lecturer_examiner_references(): void
    {
        [$studentUser, $student] = $this->createEligibleStudent();
        $student->forceFill(['access_status' => 'AwaitingDefense'])->save();

        $submission = DefenseSubmission::create([
            'student_id' => $student->id,
            'laporan_path' => 'sidang/laporan.pdf',
            'poster_path' => 'sidang/poster.pdf',
            'foto_kegiatan_1_path' => 'sidang/foto-1.pdf',
            'foto_kegiatan_2_path' => 'sidang/foto-2.pdf',
            'status' => 'Pending',
        ]);

        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        $kaprodi = Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'Kaprodi Sistem Informasi',
            'study_program' => $student->study_program,
        ]);
        $examinerOne = Lecturer::create([
            'user_id' => User::factory()->create(['role' => 'dpm'])->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'Dosen Penguji Satu',
            'study_program' => $student->study_program,
        ]);
        $examinerTwo = Lecturer::create([
            'user_id' => User::factory()->create(['role' => 'dpm'])->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'Dosen Penguji Dua',
            'study_program' => $student->study_program,
        ]);

        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/defense/{$student->id}/schedule", [
                'scheduled_date' => today()->addWeek()->toDateString(),
                'scheduled_time' => '09:00',
                'room' => 'Ruang 301',
                'dosen_penguji_1_id' => $examinerOne->id,
                'dosen_penguji_2_id' => $examinerTwo->id,
            ])
            ->assertOk();

        $submission->refresh();

        $this->assertSame('Scheduled', $submission->status);
        $this->assertSame($examinerOne->id, $submission->dosen_penguji_1_id);
        $this->assertSame($examinerTwo->id, $submission->dosen_penguji_2_id);
        $this->assertSame($kaprodi->id, $submission->scheduled_by);

        $this->actingAs($studentUser)
            ->getJson('/api/defense')
            ->assertOk()
            ->assertJsonPath('submission.dosen_penguji_1_id', $examinerOne->id)
            ->assertJsonPath('submission.dosen_penguji_2_id', $examinerTwo->id)
            ->assertJsonPath('submission.dosen_penguji_1', 'Dosen Penguji Satu')
            ->assertJsonPath('submission.dosen_penguji_2', 'Dosen Penguji Dua');
    }

    public function test_kaprodi_cannot_assign_student_dpm_as_an_examiner(): void
    {
        [, $student] = $this->createEligibleStudent();
        $student->forceFill(['access_status' => 'AwaitingDefense'])->save();

        DefenseSubmission::create([
            'student_id' => $student->id,
            'laporan_path' => 'sidang/laporan.pdf',
            'poster_path' => 'sidang/poster.pdf',
            'foto_kegiatan_1_path' => 'sidang/foto-1.pdf',
            'foto_kegiatan_2_path' => 'sidang/foto-2.pdf',
            'status' => 'Pending',
        ]);

        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'Kaprodi Sistem Informasi',
            'study_program' => $student->study_program,
        ]);
        $dpm = Lecturer::create([
            'user_id' => User::factory()->create(['role' => 'dpm'])->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'Dosen Pembimbing',
            'study_program' => $student->study_program,
        ]);
        $examiner = Lecturer::create([
            'user_id' => User::factory()->create(['role' => 'dosen_penguji'])->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'Dosen Penguji',
            'study_program' => $student->study_program,
        ]);
        $student->update(['dpm_id' => $dpm->id]);

        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/defense/{$student->id}/schedule", [
                'scheduled_date' => today()->addWeek()->toDateString(),
                'scheduled_time' => '09:00',
                'room' => 'Ruang 301',
                'dosen_penguji_1_id' => $dpm->id,
                'dosen_penguji_2_id' => $examiner->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('dosen_penguji_1_id');
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
            'name' => 'Mahasiswa Sidang',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->access_status = 'LogbookComplete';
        $student->save();

        return [$user, $student];
    }

    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'application/pdf');
    }
}
