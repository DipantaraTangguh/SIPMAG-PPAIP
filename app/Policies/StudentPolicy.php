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

    public function viewTranscript(User $user, Student $student): bool
    {
        return $user->isPpaip() || $this->sameStudyProgramKaprodi($user, $student);
    }

    public function viewTranscripts(User $user): bool
    {
        return $user->isPpaip() || $user->isKaprodi();
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
            && $student->access_status === 'MenungguSidang';
    }

    private function sameStudyProgramKaprodi(User $user, Student $student): bool
    {
        return $user->isKaprodi()
            && $user->lecturer?->study_program !== null
            && $user->lecturer->study_program === $student->study_program;
    }
}
