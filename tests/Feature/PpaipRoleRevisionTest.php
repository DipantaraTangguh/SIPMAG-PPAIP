<?php

namespace Tests\Feature;

use App\Exports\MitraApplicantsExport;
use App\Filament\Resources\Ppaip\PpaipForm1Resource;
use App\Filament\Resources\Ppaip\PpaipMitraApplicantResource;
use App\Models\Application;
use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class PpaipRoleRevisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ppaip_can_view_form1_submissions_read_only(): void
    {
        $ppaip = User::factory()->create(['role' => 'ppaip']);
        $student = $this->student('Mahasiswa Form 1');
        $student->forceFill([
            'access_status' => 'ApprovedForm1',
            'form1_data' => [
                'semester' => 6,
                'jumlahSKS' => 120,
                'ipk' => 3.75,
                'skemaMagang' => 'Magang Perusahaan',
                'topikMagang' => 'PT Contoh Indonesia',
                'outputTarget' => 'Laporan',
            ],
        ])->save();

        $this->actingAs($ppaip);

        $this->assertTrue(PpaipForm1Resource::canAccess());
        $this->assertFalse(PpaipForm1Resource::canCreate());
        $this->assertFalse(PpaipForm1Resource::canEdit($student));
        $this->assertContains($student->id, PpaipForm1Resource::getEloquentQuery()->pluck('id')->all());

        $this->get('/admin/ppaip/form1')
            ->assertOk()
            ->assertSee('Form 1')
            ->assertSee('Mahasiswa Form 1');
    }

    public function test_ppaip_can_view_mitra_applicants_and_download_cv(): void
    {
        $ppaip = User::factory()->create(['role' => 'ppaip']);
        $application = $this->mitraApplication();
        $absolutePath = $this->putPrivateFile($application->cv_file_path, 'PDF CV content');

        try {
            $this->actingAs($ppaip);

            $this->assertTrue(PpaipMitraApplicantResource::canAccess());
            $this->assertFalse(PpaipMitraApplicantResource::canCreate());
            $this->assertContains($application->id, PpaipMitraApplicantResource::getEloquentQuery()->pluck('id')->all());

            $this->get('/admin/ppaip/mitra-applicants')
                ->assertOk()
                ->assertSee('Pelamar Mitra')
                ->assertSee($application->student->name)
                ->assertSee($application->internship->company_name);

            $this->get(route('mitra-applications.cv.download', $application))
                ->assertOk();
        } finally {
            File::delete($absolutePath);
        }
    }

    public function test_non_ppaip_cannot_download_mitra_cv_or_export_applicants(): void
    {
        $application = $this->mitraApplication();
        $absolutePath = $this->putPrivateFile($application->cv_file_path, 'PDF CV content');
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);

        try {
            $this->actingAs($kaprodi)
                ->get(route('mitra-applications.cv.download', $application))
                ->assertForbidden();

            $this->actingAs($kaprodi)
                ->get(route('mitra-applications.export'))
                ->assertForbidden();
        } finally {
            File::delete($absolutePath);
        }
    }

    public function test_ppaip_can_export_mitra_applicants_to_excel_compatible_file(): void
    {
        $ppaip = User::factory()->create(['role' => 'ppaip']);
        $application = $this->mitraApplication();

        $this->actingAs($ppaip);

        $response = $this->get(route('mitra-applications.export'))
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            );

        $this->assertStringContainsString(
            '.xlsx',
            $response->headers->get('content-disposition', ''),
        );

        $xlsx = Excel::raw(new MitraApplicantsExport, ExcelWriter::XLSX);
        $tempPath = tempnam(sys_get_temp_dir(), 'mitra-applicants-').'.xlsx';
        File::put($tempPath, $xlsx);

        try {
            $sheet = IOFactory::load($tempPath)->getActiveSheet();

            $this->assertSame('Nama Mahasiswa', $sheet->getCell('E1')->getValue());
            $this->assertSame($application->student->name, $sheet->getCell('E2')->getValue());
            $this->assertSame($application->internship->company_name, $sheet->getCell('L2')->getValue());
            $this->assertSame(3.75, $sheet->getCell('J2')->getValue());
            $this->assertSame('0.00', $sheet->getStyle('J2')->getNumberFormat()->getFormatCode());
            $this->assertStringContainsString(
                basename($application->cv_file_path),
                (string) $sheet->getCell('P2')->getValue(),
            );
            $this->assertStringStartsWith('=HYPERLINK(', (string) $sheet->getCell('P2')->getValue());
        } finally {
            File::delete($tempPath);
        }
    }

    private function student(string $name): Student
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'name' => $name]);

        return Student::create([
            'user_id' => $user->id,
            'nim' => fake()->unique()->numerify('##########'),
            'name' => $name,
            'study_program' => 'Sistem Informasi',
            'email' => fake()->unique()->safeEmail(),
            'semester' => 6,
            'jumlah_sks' => 120,
            'ipk' => 3.75,
        ]);
    }

    private function mitraApplication(): Application
    {
        $student = $this->student('Mahasiswa Pelamar Mitra');

        $internship = Internship::create([
            'company_name' => 'PT Mitra Contoh',
            'position' => 'Business Analyst Intern',
            'description' => 'Program magang mitra.',
            'location' => 'Jakarta',
            'deadline' => today()->addMonth(),
            'is_active' => true,
        ]);

        return Application::create([
            'student_id' => $student->id,
            'internship_id' => $internship->id,
            'cv_file_path' => 'cv/test-mitra-applicant.pdf',
            'status' => 'Applied',
        ]);
    }

    private function putPrivateFile(string $storedPath, string $contents): string
    {
        $absolutePath = storage_path('app/private/'.$storedPath);

        if (! is_dir(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        File::put($absolutePath, $contents);

        return $absolutePath;
    }
}
