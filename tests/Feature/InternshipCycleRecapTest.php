<?php

namespace Tests\Feature;

use App\Models\InternshipCycle;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternshipCycleRecapTest extends TestCase
{
    use RefreshDatabase;

    public function test_ppaip_sees_completed_cycles_from_every_study_program(): void
    {
        $ppaipUser = User::factory()->create(['role' => 'ppaip']);
        $this->cycle('Sistem Informasi', 'RECAP-SI', 'Mahasiswa SI');
        $this->cycle('Informatika', 'RECAP-IF', 'Mahasiswa IF');

        $this->actingAs($ppaipUser)
            ->get('/admin/rekap-magang')
            ->assertOk()
            ->assertSee('Rekap Magang')
            ->assertSee('RECAP-SI')
            ->assertSee('RECAP-IF');
    }

    public function test_kaprodi_only_sees_cycles_from_their_own_study_program(): void
    {
        $kaprodiUser = $this->kaprodi('Sistem Informasi', '2222222220');
        $this->cycle('Sistem Informasi', 'RECAP-SI', 'Mahasiswa SI');
        $this->cycle('Informatika', 'RECAP-IF', 'Mahasiswa IF');

        $this->actingAs($kaprodiUser)
            ->get('/admin/rekap-magang')
            ->assertOk()
            ->assertSee('RECAP-SI')
            ->assertDontSee('RECAP-IF');
    }

    public function test_kaprodi_cannot_open_a_cycle_from_another_study_program(): void
    {
        $kaprodiUser = $this->kaprodi('Informatika', '2222222221');
        $cycle = $this->cycle('Sistem Informasi', 'RECAP-SI', 'Mahasiswa SI');

        // 404, bukan 403: getEloquentQuery() sudah membuang baris lintas prodi
        // dari scope-nya, jadi keberadaan datanya pun tidak bocor.
        $this->actingAs($kaprodiUser)
            ->get("/admin/rekap-magang/{$cycle->id}")
            ->assertNotFound();

        // Berkas LoA-nya tetap dijaga policy secara terpisah.
        $this->actingAs($kaprodiUser)
            ->get(route('rekap-magang.loa.preview', $cycle))
            ->assertForbidden();
    }

    public function test_students_and_lecturers_without_recap_access_are_blocked(): void
    {
        $dpmUser = User::factory()->create(['role' => 'dpm']);
        Lecturer::create([
            'user_id' => $dpmUser->id,
            'nidn' => '2222222222',
            'lecturer_name' => 'Dosen Pembimbing',
            'contact' => $dpmUser->email,
            'study_program' => 'Sistem Informasi',
        ]);

        $this->actingAs($dpmUser)
            ->get('/admin/rekap-magang')
            ->assertForbidden();

        $this->actingAs($dpmUser)
            ->get(route('rekap-magang.export'))
            ->assertForbidden();
    }

    public function test_export_is_scoped_to_the_kaprodi_study_program(): void
    {
        $kaprodiUser = $this->kaprodi('Sistem Informasi', '2222222223');
        $this->cycle('Sistem Informasi', 'RECAP-SI', 'Mahasiswa SI');
        $this->cycle('Informatika', 'RECAP-IF', 'Mahasiswa IF');

        $response = $this->actingAs($kaprodiUser)
            ->get(route('rekap-magang.export'))
            ->assertOk();

        $this->assertStringContainsString(
            'spreadsheetml',
            (string) $response->headers->get('content-type'),
        );

        $rows = (new \App\Exports\InternshipCyclesExport($kaprodiUser))->query()->get();
        $this->assertCount(1, $rows);
        $this->assertSame('RECAP-SI', $rows->first()->nim);
    }

    private function kaprodi(string $prodi, string $nidn): User
    {
        $user = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $user->id,
            'nidn' => $nidn,
            'lecturer_name' => 'Kaprodi '.$prodi,
            'contact' => $user->email,
            'study_program' => $prodi,
        ]);

        return $user;
    }

    private function cycle(string $prodi, string $nim, string $nama): InternshipCycle
    {
        $studentUser = User::factory()->create(['role' => 'mahasiswa']);
        $student = Student::create([
            'user_id' => $studentUser->id,
            'nim' => $nim,
            'name' => $nama,
            'study_program' => $prodi,
            'email' => strtolower($nim).'@example.test',
        ]);

        return InternshipCycle::create([
            'student_id' => $student->id,
            'cycle_number' => 1,
            'jenis_magang' => 'wajib',
            'outcome_status' => 'SiklusSelesai',
            'nim' => $nim,
            'nama' => $nama,
            'study_program' => $prodi,
            'company_name' => 'PT Contoh',
            'final_score' => 82.5,
            'letter_grade' => 'A-',
            'completed_at' => now(),
        ]);
    }
}
