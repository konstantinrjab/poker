<?php

namespace App\Models\Actions\Abstracts;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;

abstract class Action
{
    abstract public static function getName(): string;

    abstract public function updateGame(Game $game, UpdateGameRequest $request): void;
}
