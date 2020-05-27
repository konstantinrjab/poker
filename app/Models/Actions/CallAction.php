<?php

namespace App\Models\Actions;

use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

class CallAction extends Action
{
    public function updateGame(Game $game): void
    {
        $maxBet = $game->getDeal()->getRound()->getMaxBet();
        $amountToCall = $maxBet - $game->getDeal()->getRound()->getPlayerBet($this->userId);
        if ($amountToCall) {
            $game->getDeal()->getRound()->bet($this->userId, $amountToCall);
        }
    }
}
