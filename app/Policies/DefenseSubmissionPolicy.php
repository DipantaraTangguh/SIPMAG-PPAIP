<?php

namespace App\Policies;

use App\Models\DefenseSubmission;
use App\Models\User;

class DefenseSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        $lecturerId = $user->lecturer?->id;

        if (! $lecturerId) {
            return false;
        }

        return $user->isDpm()
            || $user->isDosenPenguji()
            || DefenseSubmission::query()
                ->where(function ($query) use ($lecturerId): void {
                    $query
                        ->whereHas('student', fn ($studentQuery) => $studentQuery->where('dpm_id', $lecturerId))
                        ->orWhere('dosen_penguji_1_id', $lecturerId)
                        ->orWhere('dosen_penguji_2_id', $lecturerId);
                })
                ->exists();
    }

    public function view(User $user, DefenseSubmission $submission): bool
    {
        return $user->student?->id === $submission->student_id
            || (
                $user->isKaprodi()
                && $user->lecturer?->study_program !== null
                && $user->lecturer->study_program === $submission->student?->study_program
            )
            || $this->assignedDpm($user, $submission)
            || $this->assignedExaminer($user, $submission);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DefenseSubmission $submission): bool
    {
        return false;
    }

    public function delete(User $user, DefenseSubmission $submission): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
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

    public function assess(User $user, DefenseSubmission $submission): bool
    {
        return $submission->status === 'Scheduled'
            && (
                $this->assignedDpm($user, $submission)
                || $this->assignedExaminer($user, $submission)
            );
    }

    private function assignedDpm(User $user, DefenseSubmission $submission): bool
    {
        $lecturerId = $user->lecturer?->id;

        return $lecturerId !== null
            && $submission->student?->dpm_id === $lecturerId;
    }

    private function assignedExaminer(User $user, DefenseSubmission $submission): bool
    {
        $lecturerId = $user->lecturer?->id;

        return $lecturerId !== null
            && in_array($lecturerId, [
                $submission->dosen_penguji_1_id,
                $submission->dosen_penguji_2_id,
            ], true);
    }
}
