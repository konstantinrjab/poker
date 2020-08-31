<?php

namespace App\Entities;

use App\Exceptions\GameException;
use Illuminate\Support\Collection;

class Hand extends Collection
{
    public const CARD_LIMIT = 2;

    public function __construct($items = [])
    {
        if (count($items) > self::CARD_LIMIT) {
            throw new GameException('Hand must have ' . self::CARD_LIMIT . ' cards');
        }
        parent::__construct($items);
    }

    public function add($card): void
    {
        throw new GameException('Cannot add card to Hand');
    }
}
