<?php

namespace App\Models\Actions;

use App\Models\Actions\Abstracts\Action;
use App\Models\Game;

class FoldAction extends Action
{
    public function updateGame(Game $game): void
    {
        $game->getPlayers()->getById($this->userId)->fold();
    }
}
