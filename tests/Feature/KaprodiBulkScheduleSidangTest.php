<?php

namespace Tests\Feature;

use App\Filament\Resources\Kaprodi\KaprodiDefenseScheduleResource\Pages\ListDefenseSchedule;
use App\Filament\Resources\Kaprodi\KaprodiDpmAssignmentResource\Pages\ListDpmAssignment;
use App\Filament\Resources\Kaprodi\KaprodiForm1ReviewResource\Pages\ListForm1Review;
use App\Filament\Resources\Kaprodi\KaprodiStudentResource\Pages\ListStudents;
use App\Models\DefenseSubmission;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KaprodiBulkScheduleSidangTest extends TestCase
{
    use RefreshDatabase;

    public function test_kaprodi_can_schedule_sidang_for_multiple_students_at_once(): void
    {
        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => '1111111110',
            'lecturer_name' => 'Kaprodi SI',
            'contact' => $kaprodiUser->email,
            'study_program' => 'Sistem Informasi',
        ]);

        $penguji1 = Lecturer::create([
            'user_id' => User::factory()->create(['role' => 'dosen_penguji'])->id,
            'nidn' => '1111111111',
            'lecturer_name' => 'Penguji Satu',
            'contact' => 'penguji1@example.test',
            'study_program' => 'Sistem Informasi',
        ]);
        $penguji2 = Lecturer::create([
            'user_id' => User::factory()->create(['role' => 'dosen_penguji'])->id,
            'nidn' => '1111111112',
            'lecturer_name' => 'Penguji Dua',
            'contact' => 'penguji2@example.test',
            'study_program' => 'Sistem Informasi',
        ]);

        $students = collect();
        foreach ([1, 2] as $i) {
            $student = Student::create([
                'user_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
                'nim' => 'BULKNIM'.$i,
                'name' => 'Mahasiswa Bulk '.$i,
                'study_program' => 'Sistem Informasi',
                'email' => 'bulk'.$i.'@example.test',
            ]);
            $student->forceFill(['access_status' => 'AwaitingDefense'])->save();
            DefenseSubmission::create([
                'student_id' => $student->id,
                'status' => 'Pending',
                'laporan_path' => 'test/laporan.pdf',
                'poster_path' => 'test/poster.pdf',
            ]);
            $students->push($student);
        }

        // Mahasiswa yang belum siap sidang harus dilewati, bukan ikut terjadwal.
        $notReady = Student::create([
            'user_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
            'nim' => 'BULKNIM3',
            'name' => 'Mahasiswa Belum Siap',
            'study_program' => 'Sistem Informasi',
            'email' => 'bulk3@example.test',
        ]);

        // Mahasiswa yang belum siap tidak akan pernah muncul di halaman ini,
        // jadi tidak mungkin ikut terpilih sejak awal.
        Livewire::actingAs($kaprodiUser)
            ->test(ListDefenseSchedule::class)
            ->assertCanNotSeeTableRecords([$notReady]);

        Livewire::actingAs($kaprodiUser)
            ->test(ListDefenseSchedule::class)
            ->callTableBulkAction('scheduleSidangBulk', $students, [
                'scheduled_date' => '2027-01-15',
                'scheduled_time' => '09:00',
                'room' => 'Ruang Sidang A',
                'dosen_penguji_1_id' => $penguji1->id,
                'dosen_penguji_2_id' => $penguji2->id,
            ]);

        foreach ($students as $student) {
            $student->sidangSubmission->refresh();
            $this->assertSame('Scheduled', $student->sidangSubmission->status);
            $this->assertSame('Ruang Sidang A', $student->sidangSubmission->room);
            $this->assertSame($penguji1->id, $student->sidangSubmission->dosen_penguji_1_id);
            $this->assertSame($penguji2->id, $student->sidangSubmission->dosen_penguji_2_id);
        }

        $this->assertNull($notReady->fresh()->sidangSubmission);
    }

    /**
     * Penjadwalan serentak hanya milik tumpukan Jadwal Sidang. Tumpukan lain
     * tidak pernah memuat baris yang siap dijadwalkan, jadi tombolnya di sana
     * hanya akan melaporkan semua barisnya dilewati.
     */
    public function test_bulk_scheduling_is_absent_from_the_other_kaprodi_piles(): void
    {
        $kaprodiUser = User::factory()->create(['role' => 'kaprodi']);
        Lecturer::create([
            'user_id' => $kaprodiUser->id,
            'nidn' => '1111111113',
            'lecturer_name' => 'Kaprodi SI',
            'contact' => $kaprodiUser->email,
            'study_program' => 'Sistem Informasi',
        ]);

        foreach ([ListForm1Review::class, ListDpmAssignment::class, ListStudents::class] as $page) {
            Livewire::actingAs($kaprodiUser)
                ->test($page)
                ->assertTableBulkActionDoesNotExist('scheduleSidangBulk');
        }

        Livewire::actingAs($kaprodiUser)
            ->test(ListDefenseSchedule::class)
            ->assertTableBulkActionExists('scheduleSidangBulk');
    }
}
