<?php

namespace App\Models\Actions\Abstracts;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;

abstract class Action
{
    protected string $userId;
    protected string $type;
    protected ?int $value;

    public function __construct(UpdateGameRequest $request)
    {
        $this->userId = $request->input('userId');
        $this->type = $request->input('action');
        $this->value = $request->input('value');
    }

    abstract public function updateGame(Game $game): void;
}
