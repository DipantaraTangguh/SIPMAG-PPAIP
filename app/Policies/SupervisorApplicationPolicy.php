<?php

namespace App\Policies;

use App\Models\SupervisorApplication;
use App\Models\User;

class SupervisorApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['mahasiswa', 'kaprodi'], true);
    }

    public function view(User $user, SupervisorApplication $application): bool
    {
        return $user->student?->id === $application->student_id
            || (
                $user->isKaprodi()
                && $user->lecturer?->study_program !== null
                && $user->lecturer->study_program === $application->student?->study_program
            );
    }

    public function create(User $user): bool
    {
        return $user->isMahasiswa();
    }
}
