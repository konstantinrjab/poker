<?php

namespace App\Collections;

use App\Models\Player;
use Illuminate\Support\Collection;

/**
 * @method Player[] getIterator()
 */
class PlayerCollection extends Collection
{
    public function addUser($userId)
    {
        $player = new Player($userId);
        parent::add($player);
    }
}
