<?php

namespace App\Models\Actions;

use App\Exceptions\GameException;
use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

class RaiseAction extends Action
{
    public function updateGame(Game $game): void
    {

        $maxBet = $game->getDeal()->getRound()->getMaxBet();
        // TODO: add raise amount validation
        if (false && $maxBet) {
            throw new GameException('Invalid raise amount');
        }
        $game->getDeal()->getRound()->bet($this->userId, $this->value);
        $game->getDeal()->addToPot($this->value);
        $game->getPlayers()->getById($this->userId)->pay($this->value);
        $game->getDeal()->passTurn();
    }
}
