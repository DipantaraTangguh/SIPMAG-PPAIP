<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\Logbook;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogbookReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_atomically_reconciles_counter_and_completes_logbook_stage(): void
    {
        [$dpmUser, $student] = $this->createReviewContext();

        for ($day = 1; $day <= 5; $day++) {
            $this->createLogbook($student, 'Approved', $day);
        }
        $pending = $this->createLogbook($student, 'PendingReview', 6);

        $this->actingAs($dpmUser);

        $this->postJson("/api/dpm/logbooks/{$pending->id}/approve")
            ->assertOk()
            ->assertJsonPath('approved_logbook_count', 6);

        $this->assertSame('Approved', $pending->fresh()->status);
        $this->assertSame(6, $student->fresh()->approved_logbook_count);
        $this->assertSame('LogbookComplete', $student->fresh()->access_status);
    }

    public function test_repeated_approval_does_not_increment_the_counter_twice(): void
    {
        [$dpmUser, $student] = $this->createReviewContext();
        $pending = $this->createLogbook($student);

        $this->actingAs($dpmUser);

        $this->postJson("/api/dpm/logbooks/{$pending->id}/approve")->assertOk();

        $this->postJson("/api/dpm/logbooks/{$pending->id}/approve")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('logbook');

        $this->assertSame(1, $student->fresh()->approved_logbook_count);
        $this->assertSame(1, $student->logbooks()->where('status', 'Approved')->count());
    }

    public function test_only_one_review_decision_can_be_applied(): void
    {
        [$dpmUser, $student] = $this->createReviewContext();
        $pending = $this->createLogbook($student);

        $this->actingAs($dpmUser);

        $this->postJson("/api/dpm/logbooks/{$pending->id}/reject", [
            'note' => 'Perlu diperbaiki.',
        ])->assertOk();

        $this->postJson("/api/dpm/logbooks/{$pending->id}/approve")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('logbook');

        $pending->refresh();
        $this->assertSame('Rejected', $pending->status);
        $this->assertSame('Perlu diperbaiki.', $pending->dpm_note);
        $this->assertSame(0, $student->fresh()->approved_logbook_count);
    }

    public function test_dpm_cannot_review_another_dpm_student_logbook(): void
    {
        [, $student] = $this->createReviewContext();
        [$otherDpmUser] = $this->createReviewContext('Informatika');
        $pending = $this->createLogbook($student);

        $this->actingAs($otherDpmUser);

        $this->postJson("/api/dpm/logbooks/{$pending->id}/approve")->assertNotFound();

        $this->assertSame('PendingReview', $pending->fresh()->status);
        $this->assertSame(0, $student->fresh()->approved_logbook_count);
    }

    /**
     * @return array{User, Student}
     */
    private function createReviewContext(string $studyProgram = 'Sistem Informasi'): array
    {
        $dpmUser = User::factory()->create(['role' => 'dpm']);
        $dpm = Lecturer::create([
            'user_id' => $dpmUser->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'DPM '.$studyProgram,
            'study_program' => $studyProgram,
        ]);

        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'dpm_id' => $dpm->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Bimbingan',
            'study_program' => $studyProgram,
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->access_status = 'HasDPM';
        $student->save();

        return [$dpmUser, $student];
    }

    private function createLogbook(
        Student $student,
        string $status = 'PendingReview',
        int $day = 1
    ): Logbook {
        return Logbook::create([
            'student_id' => $student->id,
            'tanggal' => now()->startOfMonth()->addDays($day - 1)->toDateString(),
            'kegiatan_harian' => 'Kegiatan magang hari '.$day,
            'hasil' => 'Hasil kegiatan hari '.$day,
            'status' => $status,
        ]);
    }
}
