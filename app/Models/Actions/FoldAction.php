<?php

namespace App\Models\Actions;

use App\Models\Actions\Abstracts\Action;
use App\Models\Round;

class FoldAction extends Action
{
    public function updateRound(Round $round): void
    {
        $round->getPlayerCollection()->getById($this->userId)->fold();
    }
}
