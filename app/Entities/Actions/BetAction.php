<?php

namespace App\Entities\Actions;

use App\Exceptions\GameException;
use App\Http\Requests\UpdateGameRequest;
use App\Entities\Actions\Action;
use App\Entities\Game;

class BetAction extends Action
{
    public static function getName(): string
    {
        return 'bet';
    }

    public function updateGame(Game $game, UpdateGameRequest $request): void
    {
        $value = $request->input('value');
        $userId = $request->input('userId');
        $minimalBet = $game->getConfig()->getBigBlind() * 2;

        if ($value < $minimalBet) {
            throw new GameException('Bet has to be greater than ' . $minimalBet);
        }
        $game->getDeal()->getRound()->bet($userId, $value);
    }
}
