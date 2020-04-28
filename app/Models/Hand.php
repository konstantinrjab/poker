<?php

namespace app\Models;

use app\Exceptions\InvalidHandException;

class Hand extends Deck
{
    protected const CARD_LIMIT = 2;

    public function addCard(Card $card): void
    {
        if ($this->count() == self::CARD_LIMIT) {
            throw new InvalidHandException("An attempt was made to add a card to a hand which had already hit it\'s legal limit of cards");
        }

        parent::addCard($card);
    }
}
