<?php

namespace App\Models;

class State
{
    public const STATUS_STARTED = 1;
    public const STATUS_END = 2;

    private int $status;

    public function setStatus(int $status)
    {
        $this->status = $status;
    }
}
