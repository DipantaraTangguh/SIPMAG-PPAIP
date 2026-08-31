<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\Logbook;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LogbookIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_only_create_one_logbook_per_date(): void
    {
        [$user, $student] = $this->createStudentWithPeriod(
            today()->subDays(10),
            today()->addDays(10)
        );
        $this->actingAs($user);

        $payload = $this->logbookPayload(today()->subDay()->toDateString());

        $this->postJson('/api/logbooks', $payload)->assertCreated();

        $this->postJson('/api/logbooks', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tanggal');

        $this->assertSame(1, $student->logbooks()->count());
    }

    public function test_logbook_date_cannot_be_in_the_future_or_outside_internship_period(): void
    {
        [$user, $student] = $this->createStudentWithPeriod(
            today()->subDays(10),
            today()->addDays(10)
        );
        $this->actingAs($user);

        foreach ([
            today()->subDays(11)->toDateString(),
            today()->addDay()->toDateString(),
        ] as $invalidDate) {
            $this->postJson('/api/logbooks', $this->logbookPayload($invalidDate))
                ->assertUnprocessable()
                ->assertJsonValidationErrors('tanggal');
        }

        $this->assertSame(0, $student->logbooks()->count());
    }

    public function test_logbook_date_cannot_be_after_completed_internship_period(): void
    {
        [$user, $student] = $this->createStudentWithPeriod(
            today()->subDays(20),
            today()->subDays(5)
        );
        $this->actingAs($user);

        $this->postJson(
            '/api/logbooks',
            $this->logbookPayload(today()->subDay()->toDateString())
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tanggal');

        $this->assertSame(0, $student->logbooks()->count());
    }

    public function test_logbook_update_cannot_reuse_another_entry_date(): void
    {
        [$user, $student] = $this->createStudentWithPeriod(
            today()->subDays(10),
            today()
        );
        $first = $this->createLogbook(
            $student,
            today()->subDays(2)->toDateString(),
            'PendingReview'
        );
        $second = $this->createLogbook(
            $student,
            today()->subDay()->toDateString(),
            'Rejected'
        );
        $this->actingAs($user);

        $this->putJson(
            "/api/logbooks/{$second->id}",
            $this->logbookPayload($first->tanggal->toDateString())
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tanggal');

        $this->assertSame(
            today()->subDay()->toDateString(),
            $second->fresh()->tanggal->toDateString()
        );
    }

    public function test_approved_count_is_derived_from_logbook_records(): void
    {
        [$user, $student] = $this->createStudentWithPeriod(
            today()->subDays(10),
            today()
        );

        $this->createLogbook($student, today()->subDays(2)->toDateString(), 'Approved');
        $this->createLogbook($student, today()->subDay()->toDateString(), 'Approved');
        $this->createLogbook($student, today()->toDateString(), 'PendingReview');

        $this->actingAs($user);

        $this->getJson('/api/logbooks')
            ->assertOk()
            ->assertJsonPath('approved_logbook_count', 2)
            ->assertJsonPath('internship_period.start_date', today()->subDays(10)->toDateString())
            ->assertJsonPath('internship_period.maximum_date', today()->toDateString());

        $this->assertFalse(Schema::hasColumn('students', 'approved_logbook_count'));
        $this->assertSame(2, $student->fresh()->approved_logbook_count);
    }

    /**
     * @return array{User, Student}
     */
    private function createStudentWithPeriod(
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $dpmUser = User::factory()->create(['role' => 'dpm']);
        $dpm = Lecturer::create([
            'user_id' => $dpmUser->id,
            'nidn' => fake()->unique()->numerify('##########'),
            'lecturer_name' => 'DPM Logbook',
            'study_program' => 'Sistem Informasi',
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'dpm_id' => $dpm->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Logbook',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->access_status = 'HasDPM';
        $student->save();

        SupervisorApplication::create([
            'student_id' => $student->id,
            'company_name' => 'PT Periode Magang',
            'company_contact' => 'Pembimbing Lapangan',
            'mulai_magang' => $startDate,
            'selesai_magang' => $endDate,
            'loa_path' => 'loa/test.pdf',
        ]);

        return [$studentUser, $student];
    }

    private function logbookPayload(string $date): array
    {
        return [
            'tanggal' => $date,
            'kegiatan_harian' => 'Mengerjakan tugas magang.',
            'hasil' => 'Tugas selesai.',
        ];
    }

    private function createLogbook(Student $student, string $date, string $status): Logbook
    {
        return Logbook::create([
            'student_id' => $student->id,
            'tanggal' => $date,
            'kegiatan_harian' => 'Kegiatan '.$date,
            'hasil' => 'Hasil '.$date,
            'status' => $status,
        ]);
    }
}
