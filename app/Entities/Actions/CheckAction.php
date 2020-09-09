<?php

namespace App\Entities\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Entities\Database\Game\Game;

class CheckAction extends Action
{
    public static function getName(): string
    {
        return 'check';
    }

    public function updateGame(Game $game, UpdateGameRequest $request): void
    {
        $userId = $request->input('userId');
        $game->getDeal()->getRound()->bet($userId, 0);
    }
}
