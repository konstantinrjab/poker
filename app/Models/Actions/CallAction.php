<?php

namespace App\Models\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

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
