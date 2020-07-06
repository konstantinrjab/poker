<?php

namespace App\Models\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

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
