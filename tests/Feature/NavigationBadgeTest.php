<?php

namespace Tests\Feature;

use App\Filament\Resources\Dpm\DpmLogbookResource;
use App\Filament\Resources\Kaprodi\KaprodiStudentResource;
use App\Filament\Resources\Ppaip\PpaipForm2Resource;
use App\Models\Form2Submission;
use App\Models\Lecturer;
use App\Models\Logbook;
use App\Models\Student;
use App\Models\SupervisorApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Badge di sidebar menjawab "ada kerjaan untuk saya", bukan "berapa yang
 * sedang berproses". Karena itu angkanya harus persis sama dengan jumlah
 * baris yang benar-benar punya tombol -- kalau meleset, badge justru
 * menyesatkan dan lebih buruk daripada tidak ada.
 */
class NavigationBadgeTest extends TestCase
{
    use RefreshDatabase;

    private const PRODI = 'Manajemen';

    public function test_kaprodi_badge_counts_only_the_three_actionable_states(): void
    {
        $kaprodi = $this->kaprodi();

        // Tiga yang menuntut tindakan.
        $this->student('4101000001', 'PendingReview');
        $this->studentAwaitingDpm('4101000002');
        $this->studentAwaitingSchedule('4101000003');

        // Yang giliran orang lain -- tidak boleh ikut terhitung.
        $this->student('4101000004', 'Unverified');
        $this->student('4101000005', 'ApprovedForm1');
        $this->student('4101000006', 'HasDPM');
        $this->student('4101000007', 'LogbookComplete');
        $this->student('4101000008', 'CycleCompleted');

        $this->actingAs($kaprodi);

        $this->assertSame('3', KaprodiStudentResource::getNavigationBadge());
    }

    public function test_kaprodi_badge_ignores_other_study_programs(): void
    {
        $kaprodi = $this->kaprodi();

        $this->student('4101000009', 'PendingReview');
        $this->student('4101000010', 'PendingReview', 'Teknik Sipil');

        $this->actingAs($kaprodi);

        $this->assertSame('1', KaprodiStudentResource::getNavigationBadge());
    }

    public function test_kaprodi_badge_is_hidden_when_there_is_nothing_to_do(): void
    {
        $kaprodi = $this->kaprodi();
        $this->student('4101000011', 'HasDPM');

        $this->actingAs($kaprodi);

        $this->assertNull(KaprodiStudentResource::getNavigationBadge());
    }

    public function test_dpm_badge_counts_students_not_pending_logbook_entries(): void
    {
        $dpmUser = User::factory()->create(['role' => 'dpm']);
        $dpm = Lecturer::create([
            'user_id' => $dpmUser->id,
            'nidn' => '0405057005',
            'lecturer_name' => 'DPM Uji',
            'study_program' => self::PRODI,
        ]);

        // Satu mahasiswa dengan tiga logbook tertunda tetap satu pekerjaan.
        $a = $this->student('4101000012', 'HasDPM');
        $a->forceFill(['dpm_id' => $dpm->id])->save();
        foreach (['2026-08-01', '2026-08-02', '2026-08-03'] as $tanggal) {
            $this->logbook($a, $tanggal, 'PendingReview');
        }

        // Sudah disetujui semua -- tidak menuntut tindakan.
        $b = $this->student('4101000013', 'HasDPM');
        $b->forceFill(['dpm_id' => $dpm->id])->save();
        $this->logbook($b, '2026-08-01', 'Approved');

        $this->actingAs($dpmUser);

        $this->assertSame('1', DpmLogbookResource::getNavigationBadge());
    }

    public function test_ppaip_badge_counts_form2_awaiting_decision(): void
    {
        $ppaip = User::factory()->create(['role' => 'ppaip']);

        $this->form2($this->student('4101000014', 'ApprovedForm1'), 'PendingReview');
        $this->form2($this->student('4101000015', 'ApprovedForm1'), 'PendingReview');
        $this->form2($this->student('4101000016', 'ApprovedForm1'), 'ApprovedForm2');

        $this->actingAs($ppaip);

        $this->assertSame('2', PpaipForm2Resource::getNavigationBadge());
    }

    private function kaprodi(): User
    {
        $user = User::factory()->create(['role' => 'kaprodi']);

        Lecturer::create([
            'user_id' => $user->id,
            'nidn' => '0301017001',
            'lecturer_name' => 'Kaprodi Uji',
            'study_program' => self::PRODI,
        ]);

        return $user->refresh();
    }

    private function student(string $nim, string $status, string $prodi = self::PRODI): Student
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $student = Student::create([
            'user_id' => $user->id,
            'nim' => $nim,
            'name' => 'Mahasiswa '.$nim,
            'study_program' => $prodi,
            'email' => $nim.'@student.test',
            'semester' => 7,
            'tahun_akademik' => '2026/2027',
            'jumlah_sks' => 120,
            'ipk' => 3.5,
        ]);

        $student->forceFill(['access_status' => $status])->save();

        return $student;
    }

    /** Sudah mengajukan pembimbing, DPM belum ditunjuk. */
    private function studentAwaitingDpm(string $nim): Student
    {
        $student = $this->student($nim, 'HasApplication');

        SupervisorApplication::create([
            'student_id' => $student->id,
            'company_name' => 'PT Contoh',
            'company_contact' => 'Kontak',
            'mulai_magang' => '2026-08-01',
            'selesai_magang' => '2026-11-01',
            'loa_path' => 'loa/uji.pdf',
        ]);

        return $student;
    }

    /** Berkas sidang masuk, jadwal belum ditetapkan. */
    private function studentAwaitingSchedule(string $nim): Student
    {
        $student = $this->student($nim, 'AwaitingDefense');

        $student->sidangSubmission()->create([
            'laporan_path' => 'sidang/laporan.pdf',
            'poster_path' => 'sidang/poster.pdf',
            'status' => 'Pending',
            'submitted_at' => now(),
        ]);

        return $student;
    }

    private function logbook(Student $student, string $tanggal, string $status): void
    {
        Logbook::create([
            'student_id' => $student->id,
            'tanggal' => $tanggal,
            'kegiatan_harian' => 'Kegiatan',
            'hasil' => 'Hasil',
            'status' => $status,
        ]);
    }

    private function form2(Student $student, string $status): void
    {
        Form2Submission::create([
            'student_id' => $student->id,
            'company_name' => 'PT Contoh',
            'alamat_perusahaan' => 'Jl. Contoh',
            'lingkup_magang' => 'Pengembangan aplikasi',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-10-01',
            'status' => $status,
            'submitted_at' => now(),
        ]);
    }
}
