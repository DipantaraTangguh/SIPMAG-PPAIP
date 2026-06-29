<?php

namespace Tests\Feature;

use App\Filament\Resources\Penguji\ExaminedSessionResource;
use App\Models\DefenseAssessment;
use App\Models\DefenseSubmission;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use App\Services\DefenseAssessmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefenseAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_dpm_and_assigned_examiners_can_assess_the_same_session(): void
    {
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [$examinerOneUser, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [$examinerTwoUser, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');
        [$unassignedUser] = $this->lecturerUser('dosen_penguji', 'Penguji Tidak Ditugaskan');

        $submission = $this->scheduledDefense($dpm, $examinerOne, $examinerTwo);

        $this->assertTrue($dpmUser->can('assess', $submission));
        $this->assertTrue($examinerOneUser->can('assess', $submission));
        $this->assertTrue($examinerTwoUser->can('assess', $submission));
        $this->assertFalse($unassignedUser->can('assess', $submission));
    }

    public function test_quick_entry_can_apply_the_same_score_to_all_components_and_remain_editable(): void
    {
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');
        $submission = $this->scheduledDefense($dpm, $examinerOne, $examinerTwo);
        $service = app(DefenseAssessmentService::class);

        $assessment = $service->save($dpmUser, $submission, [
            'internship_performance_score' => 82,
            'final_report_score' => 82,
            'presentation_score' => 82,
        ]);

        $this->assertSame('82.00', $assessment->internship_performance_score);
        $this->assertSame('82.00', $assessment->final_report_score);
        $this->assertSame('82.00', $assessment->presentation_score);
        $this->assertSame(82.0, $service->weightedScore($assessment));

        $service->save($dpmUser, $submission, [
            'internship_performance_score' => 90,
            'final_report_score' => 80,
            'presentation_score' => 70,
        ]);

        $this->assertSame(1, DefenseAssessment::query()->count());
        $this->assertSame(
            83.0,
            $service->weightedScore(DefenseAssessment::query()->firstOrFail()),
        );
    }

    public function test_final_score_is_the_average_of_three_weighted_assessments(): void
    {
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [$examinerOneUser, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [$examinerTwoUser, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');
        $submission = $this->scheduledDefense($dpm, $examinerOne, $examinerTwo);
        $service = app(DefenseAssessmentService::class);

        $service->save($dpmUser, $submission, $this->scores(80, 80, 80));
        $this->assertNull($service->finalScore($submission->fresh()));

        $service->save($examinerOneUser, $submission, $this->scores(90, 80, 70));
        $service->save($examinerTwoUser, $submission, $this->scores(70, 90, 100));

        $freshSubmission = $submission->fresh();
        $finalScore = $service->finalScore($freshSubmission);

        $this->assertSame(82.5, $service->examinerAverageScore($freshSubmission));
        $this->assertSame(81.67, $finalScore);
        $this->assertSame('A-', $service->letterGrade($finalScore));
    }

    public function test_unassigned_lecturer_cannot_save_an_assessment(): void
    {
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing Lain');
        [, $assignedDpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');
        $submission = $this->scheduledDefense($assignedDpm, $examinerOne, $examinerTwo);

        $this->expectException(AuthorizationException::class);

        app(DefenseAssessmentService::class)->save(
            $dpmUser,
            $submission,
            $this->scores(80, 80, 80),
        );
    }

    public function test_dpm_resource_only_lists_sessions_for_their_supervised_students(): void
    {
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [, $otherDpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing Lain');
        [, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');

        $assigned = $this->scheduledDefense($dpm, $examinerOne, $examinerTwo);
        $unassigned = $this->scheduledDefense($otherDpm, $examinerOne, $examinerTwo);

        $this->actingAs($dpmUser);

        $visibleIds = ExaminedSessionResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($assigned->id, $visibleIds);
        $this->assertNotContains($unassigned->id, $visibleIds);
    }

    public function test_official_university_letter_grade_boundaries_are_used(): void
    {
        $service = app(DefenseAssessmentService::class);

        $this->assertSame('A', $service->letterGrade(85));
        $this->assertSame('A-', $service->letterGrade(80));
        $this->assertSame('B+', $service->letterGrade(75));
        $this->assertSame('B', $service->letterGrade(70));
        $this->assertSame('C+', $service->letterGrade(65));
        $this->assertSame('C', $service->letterGrade(60));
        $this->assertSame('D', $service->letterGrade(50));
        $this->assertSame('E', $service->letterGrade(49.99));
    }

    /**
     * @return array{User, Lecturer}
     */
    private function lecturerUser(string $role, string $name): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'role' => $role,
        ]);

        $lecturer = Lecturer::create([
            'user_id' => $user->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => $name,
            'contact' => $user->email,
            'study_program' => 'Sistem Informasi',
        ]);

        return [$user, $lecturer];
    }

    private function scheduledDefense(
        Lecturer $dpm,
        Lecturer $examinerOne,
        Lecturer $examinerTwo,
    ): DefenseSubmission {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'dpm_id' => $dpm->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->forceFill(['access_status' => 'MenungguSidang'])->save();

        return DefenseSubmission::create([
            'student_id' => $student->id,
            'laporan_path' => 'sidang/laporan.pdf',
            'poster_path' => 'sidang/poster.pdf',
            'foto_kegiatan_1_path' => 'sidang/foto-1.pdf',
            'foto_kegiatan_2_path' => 'sidang/foto-2.pdf',
            'status' => 'Scheduled',
            'scheduled_date' => today()->addWeek()->toDateString(),
            'scheduled_time' => '09:00',
            'room' => 'Ruang 301',
            'dosen_penguji_1_id' => $examinerOne->id,
            'dosen_penguji_2_id' => $examinerTwo->id,
            'scheduled_at' => now(),
        ]);
    }

    /**
     * @return array{
     *     internship_performance_score: float,
     *     final_report_score: float,
     *     presentation_score: float
     * }
     */
    private function scores(float $performance, float $report, float $presentation): array
    {
        return [
            'internship_performance_score' => $performance,
            'final_report_score' => $report,
            'presentation_score' => $presentation,
        ];
    }
}
