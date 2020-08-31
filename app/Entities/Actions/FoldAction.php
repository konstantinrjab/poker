<?php

namespace App\Entities\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Entities\Actions\Action;
use App\Entities\Game;

class FoldAction extends Action
{
    public static function getName(): string
    {
        return 'fold';
    }

    public function updateGame(Game $game, UpdateGameRequest $request): void
    {
        $userId = $request->input('userId');

        $game->getPlayers()->getById($userId)->fold();
    }
}
