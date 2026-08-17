<?php

namespace App\Services;

use App\Exceptions\InvalidStateTransitionException;
use App\Models\Student;

/**
 * Centralised state machine for Student::access_status.
 *
 * All valid transitions are declared in TRANSITIONS.  Any attempt to jump to a
 * state that is not listed as reachable from the current state will throw an
 * InvalidStateTransitionException, making illegal status skips impossible from
 * within application code.
 *
 * Usage:
 *   app(StudentStateMachine::class)->transition($student, 'ApprovedForm1');
 *   // or with extra fillable data to save atomically:
 *   app(StudentStateMachine::class)->transition($student, 'ApprovedForm1', [
 *       'form1_approved_by' => $lecturerId,
 *       'form1_approved_at' => now(),
 *   ]);
 */
class StudentStateMachine
{
    /**
     * Map of: current state → allowed next states.
     */
    private const TRANSITIONS = [
        'Unverified'           => ['PendingReview'],
        'PendingReview'        => ['ApprovedForm1', 'RejectedForm1'],
        'RejectedForm1'        => ['PendingReview'],
        'ApprovedForm1'        => ['HasApplication', 'AwaitingConfirmation'], // non-wajib: Form 2 disetujui → konfirmasi
        'HasApplication'       => ['HasDPM', 'AwaitingConfirmation'],         // non-wajib mitra: diterima → konfirmasi LoA
        'HasDPM'               => ['LogbookComplete'],
        'LogbookComplete'      => ['AwaitingDefense'],
        'AwaitingDefense'      => ['CycleCompleted'],
        // Non-wajib jalur Form 2 wajib konfirmasi diterima (upload LoA) dulu;
        // bila ditolak perusahaan boleh mundur untuk mengajukan Form 2 lagi.
        'AwaitingConfirmation' => ['ElectiveCompleted', 'ApprovedForm1'],
        'CycleCompleted'       => ['Unverified'],  // reset siklus mandiri (magang wajib)
        'ElectiveCompleted'    => ['Unverified'],  // reset siklus mandiri (magang non-wajib)
    ];

    /**
     * Check whether a transition is valid without performing it.
     */
    public function can(Student $student, string $to): bool
    {
        $from    = $student->access_status;
        $allowed = self::TRANSITIONS[$from] ?? [];

        return in_array($to, $allowed, true);
    }

    /**
     * Perform a validated transition and persist the model.
     *
     * @param  array<string, mixed>  $extra  Additional fillable attributes to
     *                                       set on the model in the same save.
     *
     * @throws InvalidStateTransitionException
     */
    public function transition(Student $student, string $to, array $extra = []): void
    {
        if (! $this->can($student, $to)) {
            throw new InvalidStateTransitionException($student->access_status, $to);
        }

        $student->access_status = $to;

        if (! empty($extra)) {
            $student->forceFill($extra);
        }

        $student->save();
    }
}
