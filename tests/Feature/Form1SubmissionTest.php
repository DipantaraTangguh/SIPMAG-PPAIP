<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Form1SubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_1_uses_authoritative_academic_data_from_student_profile(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => '1101214230',
            'name' => 'Tangguh Dipantara',
            'study_program' => 'Sistem Informasi',
            'email' => 'tangguh@student.bakrie.ac.id',
            'semester' => '6',
            'tahun_akademik' => '2025/2026',
            'jumlah_sks' => '120',
            'ipk' => '3.75',
            'access_status' => 'Unverified',
        ]);

        $this->actingAs($user);

        $response = $this->post('/api/form1', [
            'semester' => '99',
            'jumlahSKS' => '999',
            'ipk' => '4.00',
            'skemaMagang' => 'Mitra',
            'topikMagang' => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
            'transkrip' => UploadedFile::fake()->create(
                'transkrip.pdf',
                100,
                'application/pdf',
            ),
        ]);

        $response->assertCreated();

        $student->refresh();

        $this->assertSame('6', $student->form1_data['semester']);
        $this->assertSame('120', $student->form1_data['jumlahSKS']);
        $this->assertSame('3.75', $student->form1_data['ipk']);
        $this->assertSame('PendingReview', $student->access_status);
        $this->assertTrue(Storage::disk('local')->exists($student->form1_pdf_path));
    }

    public function test_form_1_requires_a_transcript(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        Student::create([
            'user_id' => $user->id,
            'nim' => '1101214231',
            'name' => 'Mahasiswa Tanpa Transkrip',
            'study_program' => 'Sistem Informasi',
            'email' => 'student@example.test',
            'semester' => '6',
            'tahun_akademik' => '2025/2026',
            'jumlah_sks' => '120',
            'ipk' => '3.50',
            'access_status' => 'Unverified',
        ]);

        $this->actingAs($user);

        $this->postJson('/api/form1', [
            'skemaMagang' => 'Mitra',
            'topikMagang' => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('transkrip');
    }

    public function test_form_1_rejects_submission_when_academic_profile_is_incomplete(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'mahasiswa']);
        Student::create([
            'user_id' => $user->id,
            'nim' => '1101214232',
            'name' => 'Mahasiswa Data Belum Lengkap',
            'study_program' => 'Sistem Informasi',
            'email' => 'incomplete@example.test',
            'semester' => null,
            'tahun_akademik' => '2025/2026',
            'jumlah_sks' => null,
            'ipk' => null,
            'access_status' => 'Unverified',
        ]);

        $this->actingAs($user);

        $this->post('/api/form1', [
            'skemaMagang' => 'Mitra',
            'topikMagang' => 'PT Contoh Indonesia',
            'outputTarget' => 'Laporan',
            'transkrip' => UploadedFile::fake()->create(
                'transkrip.pdf',
                100,
                'application/pdf',
            ),
        ])
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Data akademik mahasiswa belum lengkap. Hubungi admin akademik sebelum mengajukan Form 1.',
            ]);
    }
}
