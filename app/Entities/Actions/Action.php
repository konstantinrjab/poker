<?php

namespace App\Entities\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Entities\Game;

abstract class Action
{
    abstract public static function getName(): string;

    abstract public function updateGame(Game $game, UpdateGameRequest $request): void;
}
