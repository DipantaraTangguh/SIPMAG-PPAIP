<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServerSideValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_form2_requires_end_date_after_start_date(): void
    {
        $user = $this->studentUser('ApprovedForm1');

        $this->actingAs($user)
            ->postJson('/api/form2', [
                'company_name' => 'PT Contoh Indonesia',
                'alamat_perusahaan' => 'Jl. Rasuna Said No. 1',
                'lingkup_magang' => 'Pengembangan aplikasi internal',
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-07-01',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['tanggal_selesai']);
    }

    public function test_supervisor_application_validates_email_phone_and_date_range_on_server(): void
    {
        Storage::fake('local');

        $user = $this->studentUser('HasApplication');

        $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post('/api/supervisor-application', [
                'company_name' => 'PT Contoh Indonesia',
                'company_contact' => 'Budi - nomor-tidak-valid',
                'nama_praktisi' => 'Budi Santoso',
                'jabatan_praktisi' => 'Engineering Manager',
                'no_telepon' => 'nomor-tidak-valid',
                'email' => 'email-tidak-valid',
                'mulai_magang' => '2026-07-01',
                'selesai_magang' => '2026-07-01',
                'loa_file' => UploadedFile::fake()->create('loa.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'no_telepon', 'selesai_magang']);
    }

    private function studentUser(string $accessStatus): User
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Validasi',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);

        $student->forceFill(['access_status' => $accessStatus])->save();

        return $user;
    }
}
