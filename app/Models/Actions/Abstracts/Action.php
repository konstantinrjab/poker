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
        $this->userId = $request->get('userId');
        $this->type = $request->get('action');
        $this->value = $request->get('value');
    }

    abstract public function updateGame(Game $game): void;
}
