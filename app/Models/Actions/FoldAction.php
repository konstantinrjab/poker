<?php

namespace App\Models\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

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
