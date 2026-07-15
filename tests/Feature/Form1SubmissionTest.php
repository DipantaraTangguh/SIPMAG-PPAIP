<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Form1SubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_1_uses_authoritative_academic_data_from_student_profile(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $user->id,
            'nim'            => '1101214230',
            'name'           => 'Tangguh Dipantara',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'tangguh@student.bakrie.ac.id',
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 120,
            'ipk'            => 3.75,
            'access_status'  => 'Unverified',
        ]);

        $this->actingAs($user);

        $response = $this->post('/api/form1', [
            'semester'     => '99',
            'jumlahSKS'    => '999',
            'ipk'          => '4.00',
            'jenisMagang'  => 'wajib',
            'skemaMagang'  => 'Magang Perusahaan',
            'topikMagang'  => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
        ]);

        $response->assertCreated();

        $student->refresh();

        $this->assertSame(6,    $student->form1_data['semester']);
        $this->assertSame(120,  $student->form1_data['jumlahSKS']);
        $this->assertSame(3.75, $student->form1_data['ipk']);
        $this->assertSame('PendingReview', $student->access_status);
    }

    public function test_form_1_rejects_old_internship_scheme_values(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        Student::create([
            'user_id'        => $user->id,
            'nim'            => '1101214233',
            'name'           => 'Mahasiswa Skema Lama',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'old-scheme@example.test',
            'semester'       => 6,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 120,
            'ipk'            => 3.50,
            'access_status'  => 'Unverified',
        ]);

        $this->actingAs($user);

        $this->withHeader('Accept', 'application/json')->post('/api/form1', [
            'jenisMagang'  => 'wajib',
            'skemaMagang'  => 'Mandiri',
            'topikMagang'  => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('skemaMagang');
    }

    public function test_form_1_rejects_submission_when_academic_profile_is_incomplete(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        Student::create([
            'user_id'        => $user->id,
            'nim'            => '1101214232',
            'name'           => 'Mahasiswa Data Belum Lengkap',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'incomplete@example.test',
            'semester'       => null,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => null,
            'ipk'            => null,
            'access_status'  => 'Unverified',
        ]);

        $this->actingAs($user);

        $this->post('/api/form1', [
            'jenisMagang'  => 'wajib',
            'skemaMagang'  => 'Magang Perusahaan',
            'topikMagang'  => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Data akademik mahasiswa belum lengkap. Hubungi admin akademik sebelum mengajukan Form 1.',
            ]);
    }

    public function test_form_1_locks_wajib_option_after_a_completed_wajib_cycle(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'        => $user->id,
            'nim'            => '1101214240',
            'name'           => 'Mahasiswa Sudah Wajib',
            'study_program'  => 'Sistem Informasi',
            'email'          => 'sudah-wajib@example.test',
            'semester'       => 7,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks'     => 130,
            'ipk'            => 3.60,
            'access_status'  => 'Unverified',
        ]);

        $student->internshipCycles()->create([
            'cycle_number'   => 1,
            'jenis_magang'   => 'wajib',
            'outcome_status' => 'SiklusSelesai',
            'nim'            => $student->nim,
            'nama'           => $student->name,
            'study_program'  => $student->study_program,
        ]);

        $this->actingAs($user);

        // Opsi wajib terkunci setelah satu siklus wajib selesai.
        $this->withHeader('Accept', 'application/json')->post('/api/form1', [
            'jenisMagang'  => 'wajib',
            'skemaMagang'  => 'Magang Perusahaan',
            'topikMagang'  => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
        ])->assertUnprocessable();

        // Non-wajib tetap boleh, berkali-kali.
        $this->post('/api/form1', [
            'jenisMagang'  => 'non_wajib',
            'skemaMagang'  => 'Magang Perusahaan',
            'topikMagang'  => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
        ])->assertCreated();

        $this->assertSame('non_wajib', $student->fresh()->form1_data['jenisMagang']);
    }

    public function test_completed_legacy_cycle_reports_status_without_fabricating_form1(): void
    {
        // The API no longer synthesizes a form1 payload for archived cycles; the
        // archived-profile view is rebuilt client-side from the student profile.
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id'      => $user->id,
            'nim'          => '1101214299',
            'name'         => 'Mahasiswa Siklus Selesai',
            'study_program' => 'Sistem Informasi',
            'email'        => 'completed-cycle@example.test',
            'semester'     => 8,
            'jumlah_sks'   => 144,
            'ipk'          => 3.80,
        ]);
        $student->forceFill([
            'access_status' => 'SiklusSelesai',
            'form1_data'    => null,
            'form1_pdf_path' => null,
        ])->save();

        $this->actingAs($user)
            ->getJson('/api/form1')
            ->assertOk()
            ->assertJsonPath('access_status', 'SiklusSelesai')
            ->assertJsonPath('form1', null);
    }
}
