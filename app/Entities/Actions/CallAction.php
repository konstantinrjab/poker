<?php

namespace App\Entities\Actions;

use App\Entities\Game\Round;
use App\Http\Requests\UpdateGameRequest;
use App\Entities\Game\Game;

class CallAction extends Action
{
    public static function getName(): string
    {
        return 'call';
    }

    public function updateGame(Game $game, UpdateGameRequest $request): void
    {
        $userId = $request->input('userId');

        $amountToCall = static::getAmountToCall($game->getDeal()->getRound(), $userId);
        if ($amountToCall) {
            $game->getDeal()->getRound()->bet($userId, $amountToCall);
        }
    }

    public static function getAmountToCall(Round $round, string $userId): int
    {
        $maxBet = $round->getMaxBet();

        return $maxBet - $round->getPlayerBet($userId);
    }
}
