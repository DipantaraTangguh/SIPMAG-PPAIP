<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidStateTransitionException extends RuntimeException
{
    /**
     * State asal & tujuan disimpan terpisah dari pesan supaya pemanggil bisa
     * memeriksanya tanpa mem-parsing string. Dipakai StudentStateMachineTest.
     */
    public readonly string $from;

    public readonly string $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;

        parent::__construct("Invalid transition: {$from} → {$to}");
    }
}
