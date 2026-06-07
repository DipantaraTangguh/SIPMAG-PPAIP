<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StoredFilePathSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_transcript_route_rejects_a_traversal_path_from_the_database(): void
    {
        $user = User::factory()->create(['role' => 'ppaip']);
        $student = Student::create([
            'user_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
            'nim' => '1101214999',
            'name' => 'Traversal Test',
            'study_program' => 'Sistem Informasi',
            'email' => 'traversal@example.test',
            'form1_pdf_path' => '../outside-transcript.txt',
        ]);

        $outsideFile = storage_path('app/outside-transcript.txt');
        File::put($outsideFile, 'must not be exposed');

        try {
            $this->actingAs($user)
                ->get(route('transkrip.download', $student))
                ->assertNotFound();
        } finally {
            File::delete($outsideFile);
        }
    }
}
