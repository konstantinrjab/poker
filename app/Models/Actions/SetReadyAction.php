<?php

namespace App\Models\Actions;

use App\Models\Actions\Abstracts\Action;
use App\Models\Round;

class SetReadyAction extends Action
{
    public function updateRound(Round $round): void
    {
        $round->getPlayers()->getById($this->userId)->setIsReady($this->value);
    }
}
