<?php

namespace Tests\Unit;

use App\Collections\Deck;
use App\Models\Card;
use App\Models\Hand;
use App\Models\HandStrength;
use PHPUnit\Framework\TestCase;

class HandStrengthTest extends TestCase
{
    public function testPair()
    {
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 3)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Spade', 2));
        $deck->add(new Card('Spade', 7));
        $deck->add(new Card('Heart', 5));
        $deck->add(new Card('Diamond', 10));
        $deck->add(new Card('Heart', 11));
        $strength = new HandStrength($hand, $deck);
        $this->assertTrue($strength->getStrength() == 102);
    }

    public function testTwoPairs()
    {
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 3)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Spade', 2));
        $deck->add(new Card('Spade', 3));
        $deck->add(new Card('Heart', 5));
        $deck->add(new Card('Diamond', 10));
        $deck->add(new Card('Heart', 11));
        $strength = new HandStrength($hand, $deck);
        $this->assertTrue($strength->getStrength() == 203);
    }

    public function testThreeOfAKind()
    {
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 4)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Spade', 2));
        $deck->add(new Card('Spade', 4));
        $deck->add(new Card('Heart', 5));
        $deck->add(new Card('Diamond', 2));
        $deck->add(new Card('Heart', 11));
        $strength = new HandStrength($hand, $deck);
        $this->assertTrue($strength->getStrength() == 302);
    }

    public function testFourOfAKind()
    {
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 10)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Spade', 2));
        $deck->add(new Card('Spade', 10));
        $deck->add(new Card('Heart', 10));
        $deck->add(new Card('Diamond', 10));
        $deck->add(new Card('Heart', 11));
        $strength = new HandStrength($hand, $deck);
        $this->assertTrue($strength->getStrength() == 610);
    }
}
