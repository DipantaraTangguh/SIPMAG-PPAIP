<?php

namespace Tests\Feature;

use App\Filament\Resources\Ppaip\PpaipInternshipResource\Pages\CreateInternship;
use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InternshipLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_ppaip_can_upload_a_company_logo(): void
    {
        Storage::fake('public');
        $ppaipUser = User::factory()->create(['role' => 'ppaip']);

        Livewire::actingAs($ppaipUser)
            ->test(CreateInternship::class)
            ->fillForm([
                'company_name' => 'PT Berlogo',
                'position' => 'Intern',
                'description' => 'Deskripsi magang',
                'location' => 'Jakarta Selatan',
                'sistem_kerja' => 'Hybrid',
                'logo_path' => [UploadedFile::fake()->image('logo.png', 200, 200)],
                'job_description' => [['item' => 'Tugas harian']],
                'skills' => [['item' => 'Excel']],
                'requirements' => [['item' => 'Semester 6 ke atas']],
                'deadline' => today()->addMonth()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $internship = Internship::where('company_name', 'PT Berlogo')->firstOrFail();

        $this->assertNotNull($internship->logo_path);
        Storage::disk('public')->assertExists($internship->logo_path);
    }

    public function test_logo_over_two_megabytes_is_rejected(): void
    {
        Storage::fake('public');
        $ppaipUser = User::factory()->create(['role' => 'ppaip']);

        Livewire::actingAs($ppaipUser)
            ->test(CreateInternship::class)
            ->fillForm([
                'company_name' => 'PT Logo Besar',
                'position' => 'Intern',
                'description' => 'Deskripsi magang',
                'location' => 'Jakarta Selatan',
                'sistem_kerja' => 'Hybrid',
                // 3MB -- di atas batas 2048 KB.
                'logo_path' => [UploadedFile::fake()->create('logo-besar.png', 3072, 'image/png')],
                'job_description' => [['item' => 'Tugas harian']],
                'skills' => [['item' => 'Excel']],
                'requirements' => [['item' => 'Semester 6 ke atas']],
                'deadline' => today()->addMonth()->toDateString(),
            ])
            ->call('create')
            ->assertHasFormErrors(['logo_path']);

        $this->assertSame(0, Internship::where('company_name', 'PT Logo Besar')->count());
    }

    public function test_api_exposes_logo_url_and_null_when_absent(): void
    {
        Storage::fake('public');

        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        Student::create([
            'user_id' => $studentUser->id,
            'nim' => '1101214299',
            'name' => 'Mahasiswa Portal',
            'study_program' => 'Sistem Informasi',
            'email' => 'portal-logo@example.test',
        ]);

        $withLogo = Internship::create([
            'company_name' => 'PT Punya Logo',
            'position' => 'Intern',
            'description' => 'Deskripsi',
            'logo_path' => 'logo-perusahaan/contoh.png',
            'deadline' => today()->addMonth(),
            'is_active' => true,
        ]);
        $tanpaLogo = Internship::create([
            'company_name' => 'PT Tanpa Logo',
            'position' => 'Intern',
            'description' => 'Deskripsi',
            'deadline' => today()->addMonth(),
            'is_active' => true,
        ]);

        $this->actingAs($studentUser)
            ->getJson("/api/internships/{$withLogo->id}")
            ->assertOk()
            ->assertJsonPath('internship.logo_url', Storage::disk('public')->url('logo-perusahaan/contoh.png'));

        $this->actingAs($studentUser)
            ->getJson("/api/internships/{$tanpaLogo->id}")
            ->assertOk()
            ->assertJsonPath('internship.logo_url', null);
    }
}
