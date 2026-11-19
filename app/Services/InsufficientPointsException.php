<?php

namespace App\Services;

class InsufficientPointsException extends \RuntimeException
{
    public function __construct(public int $needed, public int $have)
    {
        parent::__construct('Poin tidak cukup.');
    }
}
