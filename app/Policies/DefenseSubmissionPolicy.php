<?php

namespace App\Policies;

use App\Models\DefenseSubmission;
use App\Models\User;

class DefenseSubmissionPolicy
{
    public function view(User $user, DefenseSubmission $submission): bool
    {
        return $user->student?->id === $submission->student_id
            || (
                $user->isKaprodi()
                && $user->lecturer?->study_program !== null
                && $user->lecturer->study_program === $submission->student?->study_program
            );
    }

    public function schedule(User $user, DefenseSubmission $submission): bool
    {
        return $user->isKaprodi()
            && $user->lecturer?->study_program !== null
            && $user->lecturer->study_program === $submission->student?->study_program
            && $submission->status === 'Pending'
            && $submission->student?->access_status === 'MenungguSidang';
    }

    public function complete(User $user, DefenseSubmission $submission): bool
    {
        return $user->isKaprodi()
            && $user->lecturer?->study_program !== null
            && $user->lecturer->study_program === $submission->student?->study_program
            && $submission->status === 'Scheduled'
            && $submission->student?->access_status === 'MenungguSidang';
    }
}
