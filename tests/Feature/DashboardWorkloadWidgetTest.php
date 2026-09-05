<?php

namespace Tests\Feature;

use App\Filament\Widgets\RoleWorkloadOverview;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Kartu ringkasan di dashboard hanya boleh memuat pekerjaan milik peran yang
 * sedang masuk. Angkanya sendiri sudah dijaga NavigationBadgeTest -- kartu dan
 * badge memanggil method hitung yang sama -- jadi yang diuji di sini adalah
 * penggerbangan perannya dan kartu apa saja yang muncul.
 */
class DashboardWorkloadWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_kaprodi_sees_its_three_piles(): void
    {
        $user = $this->lecturerUser('kaprodi', '0301017001');

        $this->assertTrue($this->canView($user));

        Livewire::actingAs($user)
            ->test(RoleWorkloadOverview::class)
            ->assertSee('Form 1 menunggu review')
            ->assertSee('Menunggu penunjukan DPM')
            ->assertSee('Menunggu jadwal sidang')
            ->assertDontSee('Form 2 menunggu keputusan');
    }

    public function test_ppaip_sees_only_form2(): void
    {
        $user = User::factory()->create(['role' => 'ppaip']);

        $this->assertTrue($this->canView($user));

        Livewire::actingAs($user)
            ->test(RoleWorkloadOverview::class)
            ->assertSee('Form 2 menunggu keputusan')
            ->assertDontSee('Form 1 menunggu review');
    }

    public function test_dpm_sees_logbook_and_its_own_defense_assessments(): void
    {
        $user = $this->lecturerUser('dpm', '0405057005');

        Livewire::actingAs($user)
            ->test(RoleWorkloadOverview::class)
            ->assertSee('Mahasiswa dengan logbook tertunda')
            ->assertSee('Sidang menunggu penilaian Anda');
    }

    public function test_examiner_sees_only_defense_assessments(): void
    {
        $user = $this->lecturerUser('dosen_penguji', '0509068002');

        Livewire::actingAs($user)
            ->test(RoleWorkloadOverview::class)
            ->assertSee('Sidang menunggu penilaian Anda')
            ->assertDontSee('Mahasiswa dengan logbook tertunda');
    }

    /**
     * Portal mahasiswa punya dashboardnya sendiri; kalau seorang mahasiswa
     * sampai membuka panel admin, tidak ada kartu yang boleh tampil.
     */
    public function test_widget_is_hidden_from_students(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);

        Student::create([
            'user_id' => $user->id,
            'nim' => '4101000040',
            'name' => 'Mahasiswa Uji',
            'study_program' => 'Manajemen',
            'email' => 'uji-widget@student.test',
        ]);

        $this->assertFalse($this->canView($user->refresh()));
    }

    private function canView(User $user): bool
    {
        $this->actingAs($user);

        return RoleWorkloadOverview::canView();
    }

    private function lecturerUser(string $role, string $nidn): User
    {
        $user = User::factory()->create(['role' => $role]);

        Lecturer::create([
            'user_id' => $user->id,
            'nidn' => $nidn,
            'lecturer_name' => 'Dosen '.$role,
            'study_program' => 'Manajemen',
        ]);

        return $user->refresh();
    }
}
