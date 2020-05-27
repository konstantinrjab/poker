<?php

namespace App\Models\Actions;

use App\Exceptions\GameException;
use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

class BetAction extends Action
{
    public function updateGame(Game $game): void
    {
        if ($this->value < $game->getConfig()->getBigBlind()) {
            throw new GameException('Bet has to be greater than ' . $game->getConfig()->getBigBlind());
        }
        $game->getDeal()->getRound()->bet($this->userId, $this->value);
        $game->getPlayers()->getById($this->userId)->pay($this->value);
    }
}
