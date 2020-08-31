<?php

namespace App\Entities\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Entities\Actions\Action;
use App\Entities\Game;

class CallAction extends Action
{
    public static function getName(): string
    {
        return 'call';
    }

    public function updateGame(Game $game, UpdateGameRequest $request): void
    {
        $userId = $request->input('userId');

        $maxBet = $game->getDeal()->getRound()->getMaxBet();
        $amountToCall = $maxBet - $game->getDeal()->getRound()->getPlayerBet($userId);
        if ($amountToCall) {
            $game->getDeal()->getRound()->bet($userId, $amountToCall);
        }
    }
}
