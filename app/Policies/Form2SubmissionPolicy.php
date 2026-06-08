<?php

namespace App\Policies;

use App\Models\Form2Submission;
use App\Models\User;

class Form2SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isMahasiswa() || $user->isPpaip();
    }

    public function view(User $user, Form2Submission $submission): bool
    {
        return $user->isPpaip() || $user->student?->id === $submission->student_id;
    }

    public function create(User $user): bool
    {
        return $user->isMahasiswa();
    }

    public function review(User $user, Form2Submission $submission): bool
    {
        return $user->isPpaip() && $submission->status === 'PendingReview';
    }
}
