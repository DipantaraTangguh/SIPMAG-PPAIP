<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KaprodiLoaAccessTest extends TestCase
{
    use RefreshDatabase;

    private array $writtenPaths = [];

    protected function tearDown(): void
    {
        // serveStoredFile() baca langsung dari disk 'local' asli (bukan
        // Storage::fake), jadi berkas uji ditulis nyata -- bersihkan di sini.
        foreach ($this->writtenPaths as $path) {
            Storage::disk('local')->delete($path);
        }

        parent::tearDown();
    }

    private function putRealLoaFile(): string
    {
        $path = 'loa/test-'.uniqid().'.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 fake loa content');
        $this->writtenPaths[] = $path;

        return $path;
    }

    public function test_kaprodi_can_preview_and_download_loa_of_same_study_program_student(): void
    {
        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => '1234567890',
            'lecturer_name' => 'Kaprodi SI',
            'contact' => $kaprodiUser->email,
            'study_program' => 'Sistem Informasi',
        ]);

        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nim' => '1101214260',
            'name' => 'Mahasiswa LoA',
            'study_program' => 'Sistem Informasi',
            'email' => 'loa-test@example.test',
        ]);
        $student->forceFill(['access_status' => 'HasApplication'])->save();

        $loaPath = $this->putRealLoaFile();

        SupervisorApplication::create([
            'student_id' => $student->id,
            'company_name' => 'PT Contoh',
            'company_contact' => 'Budi - 08123456789',
            'loa_path' => $loaPath,
        ]);

        $this->actingAs($kaprodiUser)
            ->get(route('kaprodi.loa.preview', $student))
            ->assertOk();

        $this->actingAs($kaprodiUser)
            ->get(route('kaprodi.loa.download', $student))
            ->assertOk();
    }

    public function test_kaprodi_from_different_study_program_cannot_access_loa(): void
    {
        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => '1234567891',
            'lecturer_name' => 'Kaprodi Informatika',
            'contact' => $kaprodiUser->email,
            'study_program' => 'Informatika',
        ]);

        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nim' => '1101214261',
            'name' => 'Mahasiswa Prodi Lain',
            'study_program' => 'Sistem Informasi',
            'email' => 'loa-lain@example.test',
        ]);

        $loaPath = $this->putRealLoaFile();

        SupervisorApplication::create([
            'student_id' => $student->id,
            'company_name' => 'PT Contoh',
            'company_contact' => 'Budi - 08123456789',
            'loa_path' => $loaPath,
        ]);

        $this->actingAs($kaprodiUser)
            ->get(route('kaprodi.loa.preview', $student))
            ->assertForbidden();
    }

    public function test_loa_route_requires_supervisor_application_to_exist(): void
    {
        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => '1234567892',
            'lecturer_name' => 'Kaprodi SI',
            'contact' => $kaprodiUser->email,
            'study_program' => 'Sistem Informasi',
        ]);

        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nim' => '1101214262',
            'name' => 'Mahasiswa Belum Ajukan',
            'study_program' => 'Sistem Informasi',
            'email' => 'belum-ajukan@example.test',
        ]);

        $this->actingAs($kaprodiUser)
            ->get(route('kaprodi.loa.preview', $student))
            ->assertNotFound();
    }
}
