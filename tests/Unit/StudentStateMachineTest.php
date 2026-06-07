<?php

namespace Tests\Unit;

use App\Exceptions\InvalidStateTransitionException;
use App\Models\Student;
use App\Services\StudentStateMachine;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentStateMachineTest extends TestCase
{
    private StudentStateMachine $stateMachine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = new StudentStateMachine();
    }

    // -------------------------------------------------------------------------
    // can() — should return true for every documented valid transition
    // -------------------------------------------------------------------------

    /** @return array<string, array{string, string}> */
    public static function validTransitions(): array
    {
        return [
            'Unverified → PendingReview'      => ['Unverified', 'PendingReview'],
            'PendingReview → ApprovedForm1'   => ['PendingReview', 'ApprovedForm1'],
            'PendingReview → RejectedForm1'   => ['PendingReview', 'RejectedForm1'],
            'RejectedForm1 → PendingReview'   => ['RejectedForm1', 'PendingReview'],
            'ApprovedForm1 → HasApplication'  => ['ApprovedForm1', 'HasApplication'],
            'HasApplication → HasDPM'         => ['HasApplication', 'HasDPM'],
            'HasDPM → LogbookComplete'        => ['HasDPM', 'LogbookComplete'],
            'LogbookComplete → MenungguSidang'=> ['LogbookComplete', 'MenungguSidang'],
            'MenungguSidang → SiklusSelesai'  => ['MenungguSidang', 'SiklusSelesai'],
        ];
    }

    #[DataProvider('validTransitions')]
    public function test_can_returns_true_for_valid_transitions(string $from, string $to): void
    {
        $student = new Student();
        $student->access_status = $from;

        $this->assertTrue($this->stateMachine->can($student, $to));
    }

    // -------------------------------------------------------------------------
    // can() — should return false for illegal jumps
    // -------------------------------------------------------------------------

    /** @return array<string, array{string, string}> */
    public static function invalidTransitions(): array
    {
        return [
            'Unverified → LogbookComplete'    => ['Unverified', 'LogbookComplete'],
            'Unverified → SiklusSelesai'      => ['Unverified', 'SiklusSelesai'],
            'Unverified → HasDPM'             => ['Unverified', 'HasDPM'],
            'ApprovedForm1 → PendingReview'   => ['ApprovedForm1', 'PendingReview'],
            'HasDPM → MenungguSidang'         => ['HasDPM', 'MenungguSidang'],
            'LogbookComplete → SiklusSelesai' => ['LogbookComplete', 'SiklusSelesai'],
            'SiklusSelesai → Unverified'      => ['SiklusSelesai', 'Unverified'],
            'MenungguSidang → HasDPM'         => ['MenungguSidang', 'HasDPM'],
        ];
    }

    #[DataProvider('invalidTransitions')]
    public function test_can_returns_false_for_invalid_transitions(string $from, string $to): void
    {
        $student = new Student();
        $student->access_status = $from;

        $this->assertFalse($this->stateMachine->can($student, $to));
    }

    // -------------------------------------------------------------------------
    // transition() — valid move persists and changes the status
    // -------------------------------------------------------------------------

    public function test_transition_updates_and_saves_model_on_valid_move(): void
    {
        // Use a partial mock so we can assert save() was called without hitting DB.
        $student = $this->getMockBuilder(Student::class)
            ->onlyMethods(['save'])
            ->getMock();

        $student->access_status = 'Unverified';

        $student->expects($this->once())->method('save');

        $this->stateMachine->transition($student, 'PendingReview');

        $this->assertSame('PendingReview', $student->access_status);
    }

    public function test_transition_merges_extra_fillable_fields(): void
    {
        $student = $this->getMockBuilder(Student::class)
            ->onlyMethods(['save'])
            ->getMock();

        $student->access_status = 'PendingReview';

        $this->stateMachine->transition($student, 'ApprovedForm1', [
            'form1_approved_by' => 42,
        ]);

        $this->assertSame('ApprovedForm1', $student->access_status);
        $this->assertSame(42, $student->form1_approved_by);
    }

    // -------------------------------------------------------------------------
    // transition() — invalid move throws InvalidStateTransitionException
    // -------------------------------------------------------------------------

    #[DataProvider('invalidTransitions')]
    public function test_transition_throws_on_invalid_move(string $from, string $to): void
    {
        $student = new Student();
        $student->access_status = $from;

        $this->expectException(InvalidStateTransitionException::class);
        $this->expectExceptionMessage("Invalid transition: {$from} → {$to}");

        $this->stateMachine->transition($student, $to);
    }

    public function test_exception_carries_from_and_to_properties(): void
    {
        $student = new Student();
        $student->access_status = 'Unverified';

        try {
            $this->stateMachine->transition($student, 'SiklusSelesai');
            $this->fail('Expected InvalidStateTransitionException was not thrown.');
        } catch (InvalidStateTransitionException $e) {
            $this->assertSame('Unverified', $e->from);
            $this->assertSame('SiklusSelesai', $e->to);
        }
    }
}
