<?php

namespace Tests\Feature;

use App\Filament\Resources\Penguji\ExaminedSessionResource;
use App\Models\DefenseSubmission;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExaminerDefenseAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_examiner_resource_only_lists_sessions_assigned_to_current_lecturer(): void
    {
        [$examinerUser, $examiner] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Utama');
        [, $otherExaminer] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Lain');
        [, $thirdExaminer] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Ketiga');

        $assignedAsFirst = $this->scheduledDefense($examiner, $otherExaminer);
        $assignedAsSecond = $this->scheduledDefense($otherExaminer, $examiner);
        $unassigned = $this->scheduledDefense($otherExaminer, $thirdExaminer);

        $this->actingAs($examinerUser);

        $this->assertTrue(ExaminedSessionResource::canAccess());

        $visibleIds = ExaminedSessionResource::getEloquentQuery()
            ->pluck('id')
            ->all();

        $this->assertContains($assignedAsFirst->id, $visibleIds);
        $this->assertContains($assignedAsSecond->id, $visibleIds);
        $this->assertNotContains($unassigned->id, $visibleIds);
    }

    public function test_assigned_lecturer_can_view_but_not_modify_examined_session(): void
    {
        [$dpmUser, $dpmLecturer] = $this->lecturerUser('dpm', 'DPM Merangkap Penguji');
        [, $otherExaminer] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Lain');
        [, $thirdExaminer] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Ketiga');

        $assigned = $this->scheduledDefense($dpmLecturer, $otherExaminer);
        $unassigned = $this->scheduledDefense($otherExaminer, $thirdExaminer);

        $this->assertTrue($dpmUser->can('viewAny', DefenseSubmission::class));
        $this->assertTrue($dpmUser->can('view', $assigned));
        $this->assertFalse($dpmUser->can('view', $unassigned));
        $this->assertFalse($dpmUser->can('update', $assigned));
        $this->assertFalse($dpmUser->can('delete', $assigned));

        $this->actingAs($dpmUser);

        $this->assertTrue(ExaminedSessionResource::canAccess());
    }

    public function test_unrelated_roles_cannot_open_examined_sessions_page(): void
    {
        $user = User::factory()->create(['role' => 'ppaip']);

        $this->actingAs($user)
            ->get('/admin/penguji/defenses')
            ->assertForbidden();
    }

    public function test_dedicated_examiner_can_open_examined_sessions_page(): void
    {
        [$examinerUser, $examiner] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Admin');
        [, $otherExaminer] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Pendamping');

        $submission = $this->scheduledDefense($examiner, $otherExaminer);

        $this->actingAs($examinerUser);

        $this->assertTrue(ExaminedSessionResource::canAccess());

        $this->get('/admin/penguji/defenses')
            ->assertOk()
            ->assertSee('Sidang Diuji');

        $this->get("/admin/penguji/defenses/{$submission->id}")
            ->assertOk()
            ->assertSee('Detail Mahasiswa')
            ->assertSee('Dokumen Sidang');
    }

    public function test_examiner_document_download_requires_assigned_session(): void
    {
        [$examinerUser, $examiner] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Dokumen');
        [, $otherExaminer] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Lain');
        [, $thirdExaminer] = $this->lecturerUser('dosen_penguji', 'Dr. Penguji Ketiga');

        $storedPath = 'sidang/test-laporan-'.Str::uuid().'.pdf';
        $absolutePath = storage_path('app/private/'.$storedPath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        file_put_contents($absolutePath, 'PDF test content');

        try {
            $assigned = $this->scheduledDefense($examiner, $otherExaminer, [
                'laporan_path' => $storedPath,
            ]);
            $unassigned = $this->scheduledDefense($otherExaminer, $thirdExaminer);

            $this->actingAs($examinerUser)
                ->get(route('defense-documents.download', [
                    'submission' => $assigned,
                    'document' => 'laporan',
                ]))
                ->assertOk();

            $this->actingAs($examinerUser)
                ->get(route('defense-documents.download', [
                    'submission' => $unassigned,
                    'document' => 'laporan',
                ]))
                ->assertForbidden();
        } finally {
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }
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
     * @param  array<string, string>  $overrides
     */
    private function scheduledDefense(Lecturer $examinerOne, Lecturer $examinerTwo, array $overrides = []): DefenseSubmission
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Sidang',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->forceFill(['access_status' => 'AwaitingDefense'])->save();

        return DefenseSubmission::create(array_merge([
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
        ], $overrides));
    }
}
