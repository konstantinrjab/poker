<?php

namespace App\Models;

use Illuminate\Support\Collection;

class Deck extends Collection
{
    public static function getFull(): self
    {
        $deck = new self();
        foreach (array_keys(Card::VALUES) as $value) {
            foreach (Card::SUITS as $suit) {
                $deck->add(new Card($suit, $value));
            }
        }
        $deck->shuffle();
        return $deck;
    }
}
