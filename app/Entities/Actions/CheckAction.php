<?php

namespace App\Entities\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Entities\Actions\Action;
use App\Entities\Game;

class CheckAction extends Action
{
    public static function getName(): string
    {
        return 'check';
    }

    public function updateGame(Game $game, UpdateGameRequest $request): void
    {

    }
}
