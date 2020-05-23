<?php

namespace App\Models\Actions;

use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

class BetAction extends Action
{
    public function updateGame(Game $game): void
    {
        $game->getPlayers()->getById($this->userId)->bet($this->value);
    }
}
