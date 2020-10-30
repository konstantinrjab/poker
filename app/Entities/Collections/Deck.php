<?php

namespace App\Entities\Collections;

use App\Entities\Game\Card;
use Illuminate\Support\Collection;

class Deck extends Collection
{
    public static function getFull(): Deck
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
        for ($cardCount = 1; $cardCount <= Hand::CARDS_COUNT; $cardCount++) {
            $cards[] = $this->shift();
        }
        return new Hand($cards);
    }
}
