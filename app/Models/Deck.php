<?php

namespace app\Models;

use Exception;
use Illuminate\Support\Collection;

class Deck extends Collection
{
    public static function getFull(): self
    {
        $deck = new self();
        foreach (array_keys(Card::VALUES) as $value) {
            foreach (Card::SUITS as $suit) {
                $deck->addCard(new Card($suit, $value));
            }
        }
        return $deck;
    }

    public function takeCard(): Card
    {
        return $this->shift();
    }
}
