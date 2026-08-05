<?php

namespace App\Policies;

use App\Models\DefenseSubmission;
use App\Models\User;
use App\Services\DefenseAssessmentService;

class DefenseSubmissionPolicy
{
    public function __construct(
        private readonly DefenseAssessmentService $assessmentService,
    ) {}

    public function viewAny(User $user): bool
    {
        $lecturerId = $user->lecturer?->id;

        if (! $lecturerId) {
            return false;
        }

        return $user->isDpm()
            || $user->isDosenPenguji()
            || DefenseSubmission::query()->assessableBy($lecturerId)->exists();
    }

    public function view(User $user, DefenseSubmission $submission): bool
    {
        return $user->student?->id === $submission->student_id
            || (
                $user->isKaprodi()
                && $user->lecturer?->study_program !== null
                && $user->lecturer->study_program === $submission->student?->study_program
            )
            || $this->isAssignedAssessor($user, $submission);
    }

    public function schedule(User $user, DefenseSubmission $submission): bool
    {
        return $user->isKaprodi()
            && $user->lecturer?->study_program !== null
            && $user->lecturer->study_program === $submission->student?->study_program
            && $submission->status === 'Pending'
            && $submission->student?->access_status === 'MenungguSidang';
    }

    public function assess(User $user, DefenseSubmission $submission): bool
    {
        return $submission->status === 'Scheduled'
            && $this->isAssignedAssessor($user, $submission);
    }

    private function isAssignedAssessor(User $user, DefenseSubmission $submission): bool
    {
        return $this->assessmentService->assessorRole($user, $submission) !== null;
    }
}
