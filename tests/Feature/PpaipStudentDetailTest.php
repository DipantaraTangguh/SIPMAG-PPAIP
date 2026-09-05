<?php

namespace Tests\Feature;

use App\Filament\Resources\Ppaip\PpaipStudentResource\Pages\ListStudents;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tombol "Lihat" di daftar mahasiswa PPAIP sempat membuka modal tanpa isi:
 * resource-nya memasang ViewAction tanpa form, infolist, maupun halaman view.
 */
class PpaipStudentDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_modal_shows_what_the_table_does_not(): void
    {
        $ppaip = User::factory()->create(['role' => 'ppaip']);
        $student = $this->student();

        SupervisorApplication::create([
            'student_id' => $student->id,
            'company_name' => 'PT Tempat Magang',
            'company_contact' => 'Kontak',
            'nama_praktisi' => 'Budi Praktisi',
            'lingkup_magang' => 'Analisis data operasional',
            'mulai_magang' => '2026-08-01',
            'selesai_magang' => '2026-11-01',
            'loa_path' => 'loa/uji.pdf',
        ]);

        Livewire::actingAs($ppaip)
            ->test(ListStudents::class)
            ->mountTableAction('view', $student->id)
            // Data akademik yang tidak ada di tabel.
            ->assertSee('mahasiswa-detail@student.test')
            ->assertSee('2026/2027')
            // Isi Form 1.
            ->assertSee('Magang Perusahaan')
            ->assertSee('Analisis kinerja operasional')
            ->assertSee('Butuh jadwal fleksibel')
            // Tempat magang dari pengajuan pembimbing.
            ->assertSee('PT Tempat Magang')
            ->assertSee('Budi Praktisi');
    }

    /**
     * Seksi tempat magang hanya terisi setelah pengajuan pembimbing masuk;
     * jalur non-wajib berhenti sebelum tahap itu.
     */
    public function test_placement_section_is_hidden_without_a_supervisor_application(): void
    {
        $ppaip = User::factory()->create(['role' => 'ppaip']);
        $student = $this->student();

        Livewire::actingAs($ppaip)
            ->test(ListStudents::class)
            ->mountTableAction('view', $student->id)
            ->assertSee('Data Akademik')
            ->assertDontSee('Tempat & Periode Magang');
    }

    private function student(): Student
    {
        $student = Student::create([
            'user_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
            'nim' => '4101000050',
            'name' => 'Mahasiswa Detail',
            'study_program' => 'Manajemen',
            'email' => 'mahasiswa-detail@student.test',
            'semester' => 7,
            'tahun_akademik' => '2026/2027',
            'jumlah_sks' => 120,
            'ipk' => 3.5,
        ]);

        $student->forceFill([
            'access_status' => 'HasDPM',
            'form1_data' => [
                'jenisMagang' => 'wajib',
                'skemaMagang' => 'Magang Perusahaan',
                'topikMagang' => 'Analisis kinerja operasional',
                'outputTarget' => 'Laporan akhir',
                'catatanKhusus' => 'Butuh jadwal fleksibel',
            ],
        ])->save();

        return $student;
    }
}
