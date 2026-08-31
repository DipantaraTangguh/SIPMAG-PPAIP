<?php

namespace App\Policies;

use App\Models\Logbook;
use App\Models\User;

class LogbookPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isMahasiswa() || $user->isDpm();
    }

    public function view(User $user, Logbook $logbook): bool
    {
        return $user->student?->id === $logbook->student_id
            || $user->lecturer?->id === $logbook->student?->dpm_id;
    }

    public function create(User $user): bool
    {
        return $user->isMahasiswa();
    }

    public function review(User $user, Logbook $logbook): bool
    {
        return $user->isDpm()
            && $user->lecturer?->id === $logbook->student?->dpm_id;
    }
}
