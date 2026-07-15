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
     *
     * The special wildcard key '*' lists states reachable from *any* state
     * (currently unused, kept for forward-compatibility).
     */
    private const TRANSITIONS = [
        'Unverified'      => ['PendingReview'],
        'PendingReview'   => ['ApprovedForm1', 'RejectedForm1'],
        'RejectedForm1'   => ['PendingReview'],
        'ApprovedForm1'   => ['HasApplication', 'MenungguKonfirmasi'], // non-wajib: Form 2 approved → konfirmasi
        'HasApplication'  => ['HasDPM', 'MenungguKonfirmasi'], // non-wajib mitra: diterima → konfirmasi LoA
        'HasDPM'          => ['LogbookComplete'],
        'LogbookComplete' => ['MenungguSidang'],
        'MenungguSidang'  => ['SiklusSelesai'],
        // Non-wajib jalur Form 2 wajib konfirmasi diterima (upload LoA) dulu;
        // bila ditolak perusahaan boleh mundur untuk mengajukan Form 2 lagi.
        'MenungguKonfirmasi' => ['SelesaiNonWajib', 'ApprovedForm1'],
        'SiklusSelesai'   => ['Unverified'],     // reset siklus mandiri (magang wajib)
        'SelesaiNonWajib' => ['Unverified'],     // reset siklus mandiri (magang non-wajib)
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
