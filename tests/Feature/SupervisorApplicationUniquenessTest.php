<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupervisorApplicationUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_only_submit_one_supervisor_application(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => 'Mahasiswa Bimbingan',
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $student->forceFill(['access_status' => 'HasApplication'])->save();

        $this->actingAs($user);

        $this->post('/api/supervisor-application', $this->payload('loa-1.pdf'))
            ->assertCreated();

        $this->post('/api/supervisor-application', $this->payload('loa-2.pdf'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pengajuan sudah pernah diajukan.');

        $this->assertSame(1, $student->supervisorApplication()->count());
        $this->assertCount(1, Storage::disk('local')->allFiles('loa'));
    }

    private function payload(string $filename): array
    {
        return [
            'company_name' => 'PT Contoh Indonesia',
            'company_contact' => 'Budi - 08123456789',
            'nama_praktisi' => 'Budi Santoso',
            'jabatan_praktisi' => 'Engineering Manager',
            'no_telepon' => '08123456789',
            'email' => 'budi@example.test',
            'mulai_magang' => today()->addWeek()->toDateString(),
            'selesai_magang' => today()->addMonths(3)->toDateString(),
            'loa_file' => UploadedFile::fake()->create($filename, 100, 'application/pdf'),
        ];
    }
}
