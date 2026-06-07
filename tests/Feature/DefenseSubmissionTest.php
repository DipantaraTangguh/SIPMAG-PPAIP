<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DefenseSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_defense_submission_requires_both_activity_photos(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        Sanctum::actingAs($user);

        $this->postJson('/api/defense', [
            'laporan' => $this->pdf('laporan.pdf'),
            'poster' => $this->pdf('poster.pdf'),
            'krs' => $this->pdf('krs.pdf'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'foto_kegiatan_1',
                'foto_kegiatan_2',
            ]);

        $this->assertNull($student->sidangSubmission()->first());
        $this->assertSame([], Storage::disk('local')->allFiles('sidang'));
    }

    public function test_defense_submission_persists_all_five_documents(): void
    {
        Storage::fake('local');

        [$user, $student] = $this->createEligibleStudent();
        Sanctum::actingAs($user);

        $this->post('/api/defense', [
            'laporan' => $this->pdf('laporan.pdf'),
            'poster' => $this->pdf('poster.pdf'),
            'foto_kegiatan_1' => $this->pdf('foto-kegiatan-1.pdf'),
            'foto_kegiatan_2' => $this->pdf('foto-kegiatan-2.pdf'),
            'krs' => $this->pdf('krs.pdf'),
        ])->assertCreated();

        $submission = $student->sidangSubmission()->firstOrFail();
        $paths = [
            $submission->laporan_path,
            $submission->poster_path,
            $submission->foto_kegiatan_1_path,
            $submission->foto_kegiatan_2_path,
            $submission->krs_path,
        ];

        $this->assertCount(5, array_filter($paths));
        $this->assertCount(5, array_unique($paths));

        foreach ($paths as $path) {
            $this->assertTrue(Storage::disk('local')->exists($path));
        }

        $this->assertSame('MenungguSidang', $student->fresh()->access_status);

        $this->getJson('/api/defense')
            ->assertOk()
            ->assertJsonPath('submission.foto_kegiatan_1_path', $submission->foto_kegiatan_1_path)
            ->assertJsonPath('submission.foto_kegiatan_2_path', $submission->foto_kegiatan_2_path);
    }

    private function createEligibleStudent(): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Sidang',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
            'access_status' => 'LogbookComplete',
            'approved_logbook_count' => 6,
        ]);

        return [$user, $student];
    }

    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'application/pdf');
    }
}
