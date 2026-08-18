<?php

namespace Tests\Unit;

use App\Support\AccessStatus;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AccessStatusLabelTest extends TestCase
{
    /**
     * Setiap state yang dikenal state machine wajib punya label Indonesia,
     * supaya penambahan state baru tidak diam-diam tampil mentah di panel.
     */
    public function test_every_state_machine_state_has_an_indonesian_label(): void
    {
        $reflection = new ReflectionClass(\App\Services\StudentStateMachine::class);
        $transitions = $reflection->getConstant('TRANSITIONS');

        $states = array_unique(array_merge(
            array_keys($transitions),
            ...array_values($transitions),
        ));

        foreach ($states as $state) {
            $this->assertArrayHasKey(
                $state,
                AccessStatus::LABELS,
                "State '{$state}' belum punya label di AccessStatus::LABELS.",
            );
        }
    }

    public function test_labels_are_human_readable_not_raw_state_names(): void
    {
        foreach (AccessStatus::LABELS as $state => $label) {
            $this->assertNotSame($state, $label, "Label untuk '{$state}' masih nama state mentah.");
        }
    }

    public function test_unknown_state_falls_back_without_losing_information(): void
    {
        $this->assertSame('-', AccessStatus::label(null));
        $this->assertSame('StateTakDikenal', AccessStatus::label('StateTakDikenal'));
        $this->assertSame('gray', AccessStatus::color('StateTakDikenal'));
    }
}
