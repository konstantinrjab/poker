<?php

namespace App\Models;

class State
{
    public const STATUS_WAIT_FOR_PLAYERS = 1;
    public const STATUS_STARTED = 2;
    public const STATUS_END = 3;

    private int $status;

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
