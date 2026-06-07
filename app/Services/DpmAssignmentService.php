<?php

namespace App\Services;

use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DpmAssignmentService
{
    public function assign(Student $student, int $lecturerId): Student
    {
        return DB::transaction(function () use ($student, $lecturerId): Student {
            $lockedStudent = Student::query()
                ->lockForUpdate()
                ->findOrFail($student->id);

            if ($lockedStudent->access_status !== 'HasApplication') {
                throw ValidationException::withMessages([
                    'student_id' => 'Mahasiswa belum berada pada tahap penugasan DPM.',
                ]);
            }

            if (! $lockedStudent->supervisorApplication()->exists()) {
                throw ValidationException::withMessages([
                    'student_id' => 'Mahasiswa belum mengajukan pengajuan pembimbing.',
                ]);
            }

            if ($lockedStudent->dpm_id) {
                throw ValidationException::withMessages([
                    'student_id' => 'DPM sudah ditugaskan untuk mahasiswa ini.',
                ]);
            }

            $dpm = Lecturer::query()
                ->eligibleDpmForStudyProgram($lockedStudent->study_program)
                ->find($lecturerId);

            if (! $dpm) {
                throw ValidationException::withMessages([
                    'lecturer_id' => 'Dosen yang dipilih bukan DPM yang memenuhi syarat untuk program studi mahasiswa.',
                ]);
            }

            $lockedStudent->dpm_id = $dpm->id;
            $lockedStudent->access_status = 'HasDPM';
            $lockedStudent->save();

            return $lockedStudent->refresh();
        });
    }
}
