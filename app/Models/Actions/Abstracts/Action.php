<?php

namespace App\Models\Actions\Abstracts;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Round;

abstract class Action
{
    protected string $playerId;
    protected string $type;
    protected ?int $value;

    public function __construct(UpdateGameRequest $request)
    {
        $this->playerId = $request->get('userId');
        $this->type = $request->get('action');
        $this->value = $request->get('value');
    }

    abstract public function updateRound(Round $round): void;
}
