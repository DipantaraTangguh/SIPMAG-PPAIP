<?php

namespace Tests\Feature;

use App\Models\DefenseSubmission;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use App\Services\DefenseAssessmentService;
use App\Services\InternshipCycleCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleCompletionAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cycle_cannot_be_completed_until_all_three_assessors_have_scored(): void
    {
        [$kaprodiUser] = $this->lecturerUser('kaprodi', 'Kaprodi');
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [$examinerOneUser, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');
        [$student, $submission] = $this->scheduledDefense($dpm, $examinerOne, $examinerTwo);
        $assessmentService = app(DefenseAssessmentService::class);

        $assessmentService->save($dpmUser, $submission, $this->scores(80));
        $assessmentService->save($examinerOneUser, $submission, $this->scores(82));

        $this->assertFalse(app(InternshipCycleCompletionService::class)->canComplete($student));

        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/defense/{$student->id}/complete")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('assessments');

        $this->assertSame('MenungguSidang', $student->fresh()->access_status);
    }

    public function test_cycle_can_be_completed_after_all_three_assessments_are_available(): void
    {
        [$kaprodiUser] = $this->lecturerUser('kaprodi', 'Kaprodi');
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [$examinerOneUser, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [$examinerTwoUser, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');
        [$student, $submission] = $this->scheduledDefense($dpm, $examinerOne, $examinerTwo);
        $assessmentService = app(DefenseAssessmentService::class);
        $form1Data = [
            'semester' => 6,
            'jumlahSKS' => 120,
            'ipk' => 3.75,
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'PT Contoh',
            'outputTarget' => 'Laporan Akhir',
        ];
        $student->update([
            'form1_data' => $form1Data,
            'form1_pdf_path' => 'transkrip/form1.pdf',
        ]);

        $assessmentService->save($dpmUser, $submission, $this->scores(80));
        $assessmentService->save($examinerOneUser, $submission, $this->scores(82));
        $assessmentService->save($examinerTwoUser, $submission, $this->scores(84));

        $this->assertTrue(app(InternshipCycleCompletionService::class)->canComplete($student));

        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/defense/{$student->id}/complete")
            ->assertOk()
            ->assertJsonPath('access_status', 'SiklusSelesai')
            ->assertJsonPath('final_score', 82);

        $student->refresh();

        $this->assertSame('SiklusSelesai', $student->access_status);
        $this->assertNull($student->dpm_id);
        $this->assertSame($form1Data, $student->form1_data);
        $this->assertSame('transkrip/form1.pdf', $student->form1_pdf_path);
        $this->assertSame(3, $submission->assessments()->count());

        // Penyelesaian siklus wajib harus terekam permanen di riwayat.
        $cycle = $student->internshipCycles()->first();
        $this->assertNotNull($cycle);
        $this->assertSame(1, $cycle->cycle_number);
        $this->assertSame('wajib', $cycle->jenis_magang);
        $this->assertSame('SiklusSelesai', $cycle->outcome_status);
        $this->assertSame($student->nim, $cycle->nim);
        $this->assertSame(82.0, $cycle->final_score);
        $this->assertSame('A-', $cycle->letter_grade);
    }

    public function test_ppaip_can_see_assessment_progress_and_final_score(): void
    {
        $ppaipUser = User::factory()->create(['role' => 'ppaip']);
        [$dpmUser, $dpm] = $this->lecturerUser('dpm', 'Dosen Pembimbing');
        [$examinerOneUser, $examinerOne] = $this->lecturerUser('dosen_penguji', 'Penguji Satu');
        [$examinerTwoUser, $examinerTwo] = $this->lecturerUser('dosen_penguji', 'Penguji Dua');
        [, $submission] = $this->scheduledDefense($dpm, $examinerOne, $examinerTwo);
        $assessmentService = app(DefenseAssessmentService::class);

        $assessmentService->save($dpmUser, $submission, $this->scores(80));
        $assessmentService->save($examinerOneUser, $submission, $this->scores(82));
        $assessmentService->save($examinerTwoUser, $submission, $this->scores(84));

        $this->actingAs($ppaipUser)
            ->get('/admin/ppaip/students')
            ->assertOk()
            ->assertSee('Penilaian Sidang')
            ->assertSee('3/3')
            ->assertSee('82.00 (A-)')
            ->assertSee('Selesaikan Siklus');
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

    /**
     * @return array{Student, DefenseSubmission}
     */
    private function scheduledDefense(
        Lecturer $dpm,
        Lecturer $examinerOne,
        Lecturer $examinerTwo,
    ): array {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'dpm_id' => $dpm->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Penilaian Lengkap',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->forceFill(['access_status' => 'MenungguSidang'])->save();

        $submission = DefenseSubmission::create([
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

        return [$student, $submission];
    }

    /**
     * @return array{
     *     internship_performance_score: float,
     *     final_report_score: float,
     *     presentation_score: float
     * }
     */
    private function scores(float $score): array
    {
        return [
            'internship_performance_score' => $score,
            'final_report_score' => $score,
            'presentation_score' => $score,
        ];
    }
}
