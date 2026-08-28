<?php

namespace Tests\Feature;

use App\Models\Form2Submission;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Models\User;
use App\Services\InternshipCycleSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tempat magang di rekap harus datang dari pengajuan pembimbing magang --
 * satu-satunya berkas yang menyatakan mahasiswa diterima di mana. Form 2
 * cuma surat pengantar ke perusahaan yang dituju, dan perusahaan itu belum
 * tentu yang akhirnya menerima.
 */
class CyclePlacementSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wajib_cycle_records_the_company_that_accepted_not_the_one_asked(): void
    {
        $student = $this->student('wajib');

        // Surat pengantar dikirim ke PT Pengantar...
        $this->approvedForm2($student, 'PT Pengantar');

        // ...tapi yang menerima PT Penerima, dan itu yang dilaporkan
        // mahasiswa bersama LoA-nya.
        SupervisorApplication::create([
            'student_id' => $student->id,
            'company_name' => 'PT Penerima',
            'company_contact' => 'Kontak',
            'nama_praktisi' => 'Budi Praktisi',
            'mulai_magang' => '2026-09-01',
            'selesai_magang' => '2026-12-01',
            'loa_path' => 'loa/uji.pdf',
        ]);

        $cycle = app(InternshipCycleSnapshotService::class)->record($student->refresh());

        $this->assertSame('PT Penerima', $cycle->company_name);
        $this->assertSame('Budi Praktisi', $cycle->nama_pimpinan);
    }

    public function test_non_wajib_cycle_still_falls_back_to_form2(): void
    {
        // Magang non-wajib tidak pernah punya pengajuan pembimbing --
        // SupervisorController menolaknya -- jadi Form 2 tetap sumbernya.
        $student = $this->student('non_wajib');
        $this->approvedForm2($student, 'PT Pengantar');

        $cycle = app(InternshipCycleSnapshotService::class)->record($student->refresh());

        $this->assertSame('PT Pengantar', $cycle->company_name);
    }

    private function student(string $jenisMagang): Student
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $student = Student::create([
            'user_id' => $user->id,
            'nim' => '4101000099',
            'name' => 'Mahasiswa Uji',
            'study_program' => 'Manajemen',
            'email' => 'uji@student.test',
            'semester' => 7,
            'tahun_akademik' => '2026/2027',
            'jumlah_sks' => 120,
            'ipk' => 3.5,
        ]);

        $student->update(['form1_data' => ['jenisMagang' => $jenisMagang]]);

        return $student;
    }

    private function approvedForm2(Student $student, string $companyName): void
    {
        Form2Submission::create([
            'student_id' => $student->id,
            'company_name' => $companyName,
            'alamat_perusahaan' => 'Jl. Contoh',
            'lingkup_magang' => 'Pengembangan aplikasi',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-10-01',
            'status' => 'ApprovedForm2',
            'submitted_at' => now(),
        ]);
    }
}
