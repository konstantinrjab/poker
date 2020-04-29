<?php

namespace App\Models;

use App\Exceptions\InvalidHandException;

class Hand extends Deck
{
    public const CARD_LIMIT = 2;

    public function add($card): void
    {
        if ($this->count() == self::CARD_LIMIT) {
            throw new InvalidHandException("An attempt was made to add a card to a hand which had already hit it\'s legal limit of cards");
        }

        parent::add($card);
    }
}
