<?php

namespace Tests\Feature;

use App\Models\Form2Submission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CycleResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_reset_cycle_after_non_wajib_completion(): void
    {
        [$user, $student] = $this->completedStudent('SelesaiNonWajib', 'non_wajib');

        $form2 = Form2Submission::create([
            'student_id' => $student->id,
            'company_name' => 'PT Contoh',
            'alamat_perusahaan' => 'Jl. Contoh No. 1',
            'lingkup_magang' => 'Magang',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-10-01',
            'status' => 'ApprovedForm2',
        ]);

        $this->actingAs($user)
            ->postJson('/api/student/cycle/reset')
            ->assertOk()
            ->assertJsonPath('access_status', 'Unverified');

        $student->refresh();

        // Field siklus dibersihkan, mahasiswa kembali ke awal.
        $this->assertSame('Unverified', $student->access_status);
        $this->assertNull($student->form1_data);
        $this->assertNull($student->form1_pdf_path);
        $this->assertNull($student->form1_approved_at);
        $this->assertNull($student->dpm_id);
        $this->assertFalse($student->is_independent);

        // Child record siklus lama diarsipkan (soft delete), riwayat tetap ada.
        $this->assertSoftDeleted('form2_submissions', ['id' => $form2->id]);
        $this->assertSame(1, $student->internshipCycles()->count());

        // Setelah reset boleh mengajukan Form 1 lagi (non-wajib kedua).
        // Refresh: relasi student di instance user stale setelah reset.
        $user->refresh();
        $this->actingAs($user)->post('/api/form1', [
            'jenisMagang'  => 'non_wajib',
            'skemaMagang'  => 'Magang Perusahaan',
            'topikMagang'  => 'PT Berikutnya',
            'outputTarget' => 'Laporan',
        ])->assertCreated();
    }

    public function test_student_can_reset_cycle_after_wajib_completion(): void
    {
        [$user, $student] = $this->completedStudent('SiklusSelesai', 'wajib');

        $this->actingAs($user)
            ->postJson('/api/student/cycle/reset')
            ->assertOk();

        $student->refresh();
        $this->assertSame('Unverified', $student->access_status);

        // Riwayat wajib tetap tercatat → opsi wajib terkunci di Form 1 berikutnya.
        $user->refresh();
        $this->actingAs($user)->withHeader('Accept', 'application/json')->post('/api/form1', [
            'jenisMagang'  => 'wajib',
            'skemaMagang'  => 'Magang Perusahaan',
            'topikMagang'  => 'PT Coba Wajib Lagi',
            'outputTarget' => 'Laporan',
        ])->assertUnprocessable();
    }

    public function test_reset_is_rejected_when_cycle_is_not_finished(): void
    {
        [$user, $student] = $this->completedStudent('ApprovedForm1', 'wajib', recordHistory: false);

        $this->actingAs($user)
            ->postJson('/api/student/cycle/reset')
            ->assertForbidden();

        $this->assertSame('ApprovedForm1', $student->fresh()->access_status);
    }

    public function test_history_endpoint_lists_completed_cycles(): void
    {
        [$user, $student] = $this->completedStudent('SelesaiNonWajib', 'non_wajib');

        $this->actingAs($user)
            ->getJson('/api/student/cycle/history')
            ->assertOk()
            ->assertJsonCount(1, 'cycles')
            ->assertJsonPath('cycles.0.jenis_magang', 'non_wajib')
            ->assertJsonPath('cycles.0.cycle_number', 1);
    }

    /**
     * @return array{User, Student}
     */
    private function completedStudent(string $status, string $jenis, bool $recordHistory = true): array
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $user->id,
            'nim'            => fake()->unique()->numerify('##########'),
            'name'           => 'Mahasiswa Reset',
            'study_program'  => 'Sistem Informasi',
            'email'          => fake()->unique()->safeEmail(),
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 110,
            'ipk'            => 3.40,
        ]);
        $student->forceFill([
            'access_status' => $status,
            'form1_data' => ['jenisMagang' => $jenis, 'skemaMagang' => 'Magang Perusahaan'],
            'form1_pdf_path' => 'transkrip/form1.pdf',
            'form1_approved_at' => now(),
        ])->save();

        if ($recordHistory) {
            $student->internshipCycles()->create([
                'cycle_number'   => 1,
                'jenis_magang'   => $jenis,
                'outcome_status' => $jenis === 'wajib' ? 'SiklusSelesai' : 'SelesaiNonWajib',
                'nim'            => $student->nim,
                'nama'           => $student->name,
                'study_program'  => $student->study_program,
            ]);
        }

        return [$user, $student];
    }
}
