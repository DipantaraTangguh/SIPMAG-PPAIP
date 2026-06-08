<?php

namespace App\Policies;

use App\Models\Internship;
use App\Models\User;

class InternshipPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isPpaip();
    }

    public function update(User $user, Internship $internship): bool
    {
        return $user->isPpaip();
    }

    public function delete(User $user, Internship $internship): bool
    {
        return $user->isPpaip();
    }
}
