<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isMahasiswa() || $user->isPpaip();
    }

    public function view(User $user, Application $application): bool
    {
        return $user->isPpaip()
            || $user->student?->id === $application->student_id;
    }

    public function create(User $user): bool
    {
        return $user->isMahasiswa();
    }
}
