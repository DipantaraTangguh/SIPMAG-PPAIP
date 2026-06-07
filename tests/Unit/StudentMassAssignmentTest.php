<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensitive_workflow_fields_cannot_be_mass_assigned(): void
    {
        $student = Student::create([
            'user_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
            'nim' => '1101214888',
            'name' => 'Mass Assignment Test',
            'study_program' => 'Sistem Informasi',
            'email' => 'mass-assignment@example.test',
            'access_status' => 'SiklusSelesai',
            'form1_approved_by' => 999,
            'form1_approved_at' => now(),
        ]);

        $student->refresh();

        $this->assertSame('Unverified', $student->access_status);
        $this->assertNull($student->form1_approved_by);
        $this->assertNull($student->form1_approved_at);

        $student->update([
            'name' => 'Updated Safe Field',
            'access_status' => 'ApprovedForm1',
            'form1_approved_by' => 999,
            'form1_approved_at' => now(),
        ]);

        $student->refresh();

        $this->assertSame('Updated Safe Field', $student->name);
        $this->assertSame('Unverified', $student->access_status);
        $this->assertNull($student->form1_approved_by);
        $this->assertNull($student->form1_approved_at);
    }
}
