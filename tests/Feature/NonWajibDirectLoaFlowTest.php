<?php

namespace Tests\Feature;

use App\Models\InternshipCycle;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Alur magang non-wajib: Form 1 -> (disetujui) -> unggah LoA -> selesai.
 * Tidak lewat Form 2, DPM, logbook, maupun sidang.
 */
class NonWajibDirectLoaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_wajib_can_upload_loa_immediately_after_form1_approval(): void
    {
        [$studentUser, $student] = $this->student('2101214300');
        $kaprodiUser = $this->kaprodi();

        // Sudah pernah menyelesaikan magang wajib lalu reset siklus.
        InternshipCycle::create([
            'student_id' => $student->id,
            'cycle_number' => 1,
            'jenis_magang' => 'wajib',
            'outcome_status' => 'CycleCompleted',
            'nim' => $student->nim,
            'nama' => $student->name,
            'study_program' => $student->study_program,
            'final_score' => 85.0,
            'letter_grade' => 'A',
            'completed_at' => now(),
        ]);

        // 1. Isi Form 1 non-wajib.
        $studentUser->refresh();
        $this->actingAs($studentUser)->postJson('/api/form1', [
            'jenisMagang' => 'non_wajib',
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'PT Contoh',
            'outputTarget' => 'Laporan',
        ])->assertCreated();

        // 2. Kaprodi menyetujui -> langsung masuk tahap konfirmasi.
        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk()
            ->assertJsonPath('access_status', 'AwaitingConfirmation');

        $this->assertSame('AwaitingConfirmation', $student->fresh()->access_status);

        // 3. Langsung unggah LoA, tanpa Form 2 sama sekali.
        Storage::fake('local');
        $studentUser->refresh();
        $this->actingAs($studentUser)->post('/api/student/cycle/confirm', [
            'hasil' => 'diterima',
            'company_name' => 'PT Tempat Sendiri',
            'tanggal_mulai' => '2026-09',
            'tanggal_selesai' => '2026-11',
            'loa_file' => UploadedFile::fake()->create('loa.pdf', 100, 'application/pdf'),
        ])->assertOk()->assertJsonPath('access_status', 'ElectiveCompleted');

        $student->refresh();
        $this->assertSame('ElectiveCompleted', $student->access_status);
        $this->assertNull($student->dpm_id);
        $this->assertSame(0, $student->form2Submissions()->count());
        $this->assertSame(2, $student->internshipCycles()->count());
    }

    public function test_wajib_still_stops_at_approved_form1(): void
    {
        [$studentUser, $student] = $this->student('2101214301');
        $kaprodiUser = $this->kaprodi();

        $this->actingAs($studentUser)->postJson('/api/form1', [
            'jenisMagang' => 'wajib',
            'skemaMagang' => 'Magang Perusahaan',
            'topikMagang' => 'PT Contoh',
            'outputTarget' => 'Laporan',
        ])->assertCreated();

        $this->actingAs($kaprodiUser)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk()
            ->assertJsonPath('access_status', 'ApprovedForm1');

        $this->assertSame('ApprovedForm1', $student->fresh()->access_status);
    }

    /**
     * @return array{User, Student}
     */
    private function student(string $nim): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => $nim,
            'name' => 'Mahasiswa '.$nim,
            'study_program' => 'Sistem Informasi',
            'email' => $nim.'@example.test',
            'semester' => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks' => 120,
            'ipk' => 3.5,
        ]);

        return [$user, $student];
    }

    private function kaprodi(): User
    {
        $user = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $user->id,
            'nidn' => '7770001111',
            'lecturer_name' => 'Kaprodi SI',
            'contact' => $user->email,
            'study_program' => 'Sistem Informasi',
        ]);

        return $user;
    }
}
