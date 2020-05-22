<?php

namespace App\Models\Actions;

use App\Models\Actions\Abstracts\Action;
use App\Models\Round;

class CheckAction extends Action
{
    public function updateRound(Round $round): void
    {
        $round->getPlayers()->setNextActivePlayer();
    }
}
