<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidStateTransitionException extends RuntimeException
{
    public readonly string $from;
    public readonly string $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to   = $to;

        parent::__construct("Invalid transition: {$from} → {$to}");
    }
}
