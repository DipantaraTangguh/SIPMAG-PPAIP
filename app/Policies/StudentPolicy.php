<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['ppaip', 'kaprodi', 'dpm'], true);
    }

    public function view(User $user, Student $student): bool
    {
        return $user->student?->is($student)
            || $this->sameStudyProgramKaprodi($user, $student)
            || $user->isPpaip()
            || $user->lecturer?->id === $student->dpm_id;
    }



    public function resetCycle(User $user, Student $student): bool
    {
        return $user->student?->is($student) === true
            && in_array($student->access_status, ['CycleCompleted', 'ElectiveCompleted'], true);
    }

    public function reviewForm1(User $user, Student $student): bool
    {
        return $this->sameStudyProgramKaprodi($user, $student)
            && $student->access_status === 'PendingReview';
    }

    public function assignDpm(User $user, Student $student): bool
    {
        return $this->sameStudyProgramKaprodi($user, $student);
    }

    public function manageDefense(User $user, Student $student): bool
    {
        return $this->sameStudyProgramKaprodi($user, $student)
            && $student->access_status === 'AwaitingDefense';
    }

    /**
     * Berlaku untuk Kaprodi maupun Staff Prodi yang bertindak atas namanya.
     * Keduanya ber-role kaprodi; bedanya hanya dari mana program studinya
     * diambil, dan itu sudah ditangani resolveStudyProgram().
     */
    private function sameStudyProgramKaprodi(User $user, Student $student): bool
    {
        $studyProgram = $user->resolveStudyProgram();

        return $user->isKaprodi()
            && $studyProgram !== null
            && $studyProgram === $student->study_program;
    }
}
