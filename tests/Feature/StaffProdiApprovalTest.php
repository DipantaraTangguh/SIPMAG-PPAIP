<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Staff Prodi bekerja atas nama Kaprodi.
 *
 * Kewenangannya identik, tapi identitas yang tercetak di Surat Keterangan
 * harus tetap Kaprodi -- staff bukan dosen dan tidak punya NIDN, jadi
 * namanya tidak sah muncul sebagai penandatangan dokumen resmi.
 */
class StaffProdiApprovalTest extends TestCase
{
    use RefreshDatabase;

    private const PRODI = 'Manajemen';

    public function test_staff_prodi_sees_students_of_its_study_program(): void
    {
        $this->createKaprodi();
        $staff = $this->createStaff();
        $this->createPendingStudent('3201000001');

        // Mahasiswa prodi lain tidak boleh ikut terlihat.
        $this->createPendingStudent('3201000002', 'Teknik Sipil');

        $response = $this->actingAs($staff)
            ->getJson('/api/kaprodi/form1')
            ->assertOk();

        $nims = collect($response->json('submissions.data') ?? $response->json('submissions'))
            ->pluck('nim');

        $this->assertTrue($nims->contains('3201000001'));
        $this->assertFalse($nims->contains('3201000002'));
    }

    public function test_approval_by_staff_is_signed_by_the_kaprodi(): void
    {
        $kaprodi = $this->createKaprodi();
        $staff = $this->createStaff();
        $student = $this->createPendingStudent('3201000003');

        $this->actingAs($staff)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk()
            ->assertJsonPath('access_status', 'ApprovedForm1');

        $student->refresh();

        // Yang tercetak di surat: Kaprodi, bukan staff.
        $this->assertSame($kaprodi->lecturer->id, $student->form1_approved_by);
        $this->assertSame('Kaprodi Manajemen', $student->form1Approver->lecturer_name);
    }

    public function test_approval_by_kaprodi_is_signed_by_itself(): void
    {
        $kaprodi = $this->createKaprodi();
        $student = $this->createPendingStudent('3201000004');

        $this->actingAs($kaprodi)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertOk();

        $student->refresh();

        $this->assertSame($kaprodi->lecturer->id, $student->form1_approved_by);
    }

    public function test_approval_is_refused_when_the_study_program_has_no_kaprodi(): void
    {
        // Sengaja tanpa Kaprodi: penandatangannya tidak bisa ditentukan.
        $staff = $this->createStaff();
        $student = $this->createPendingStudent('3201000005');

        $this->actingAs($staff)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertStatus(422);

        $this->assertSame('PendingReview', $student->fresh()->access_status);
    }

    public function test_staff_cannot_touch_another_study_program(): void
    {
        $this->createKaprodi();
        $staff = $this->createStaff();
        $student = $this->createPendingStudent('3201000006', 'Teknik Sipil');

        $this->actingAs($staff)
            ->postJson("/api/kaprodi/form1/{$student->id}/approve")
            ->assertForbidden();

        $this->assertSame('PendingReview', $student->fresh()->access_status);
    }

    public function test_staff_is_not_offered_as_a_lecturer(): void
    {
        $this->createKaprodi();
        $staff = $this->createStaff();

        // Staff tidak boleh punya baris dosen sama sekali -- itu yang menjaga
        // mereka keluar dari daftar DPM maupun dosen penguji, sekaligus
        // menghindari NIDN karangan di tabel yang memasok dokumen resmi.
        $this->assertNull($staff->lecturer);
        $this->assertSame(0, Lecturer::where('lecturer_name', 'Staff Manajemen')->count());
        $this->assertTrue($staff->isStaffProdi());
    }

    private function createKaprodi(): User
    {
        $user = User::factory()->create([
            'role' => 'kaprodi',
            'name' => 'Kaprodi Manajemen',
        ]);

        Lecturer::create([
            'user_id' => $user->id,
            'nidn' => '0301017001',
            'lecturer_name' => 'Kaprodi Manajemen',
            'contact' => $user->email,
            'study_program' => self::PRODI,
        ]);

        return $user->refresh();
    }

    private function createStaff(): User
    {
        return User::factory()->create([
            'role' => 'kaprodi',
            'name' => 'Staff Manajemen',
            'study_program' => self::PRODI,
        ]);
    }

    private function createPendingStudent(string $nim, string $studyProgram = self::PRODI): Student
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        $student = Student::create([
            'user_id' => $user->id,
            'nim' => $nim,
            'name' => 'Mahasiswa '.$nim,
            'study_program' => $studyProgram,
            'email' => $nim.'@student.test',
            'semester' => 7,
            'tahun_akademik' => '2026/2027',
            'jumlah_sks' => 120,
            'ipk' => 3.5,
            'form1_data' => [
                'semester' => 7,
                'jumlahSKS' => 120,
                'ipk' => 3.5,
                'jenisMagang' => 'wajib',
                'skemaMagang' => 'Magang Perusahaan',
                'topikMagang' => 'PT Contoh',
                'outputTarget' => 'Laporan',
            ],
        ]);

        $student->forceFill(['access_status' => 'PendingReview'])->save();

        return $student;
    }
}
