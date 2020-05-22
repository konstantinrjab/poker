<?php

namespace App\Collections;

use App\Models\Card;
use App\Models\Hand;
use Illuminate\Support\Collection;

class Deck extends Collection
{
    public static function getFull(): self
    {
        $deck = new static();
        foreach (array_keys(Card::VALUES) as $value) {
            foreach (Card::SUITS as $suit) {
                $deck->add(new Card($suit, $value));
            }
        }
        return $deck->shuffle();
    }

    public function getHand(): Hand
    {
        $cards = [];
        for ($cardCount = 1; $cardCount <= Hand::CARD_LIMIT; $cardCount++) {
            $cards[] = $this->shift();
        }
        return new Hand($cards);
    }
}
