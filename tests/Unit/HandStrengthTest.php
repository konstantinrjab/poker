<?php

namespace Tests\Unit;

use App\Entities\Collections\Deck;
use App\Entities\Card;
use App\Entities\Collections\Hand;
use App\Entities\HandStrength;
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

        $this->assertTrue(max($strength->getStrength()) == 102);
        $this->assertMatchesRegularExpression('/pair/i', $strength->getStrengthDescription());
        $this->assertMatchesRegularExpression('/two/i', $strength->getStrengthDescription());
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

        $this->assertTrue(max($strength->getStrength()) == 203);
        $this->assertMatchesRegularExpression('/two/i', $strength->getStrengthDescription());
        $this->assertMatchesRegularExpression('/pair/i', $strength->getStrengthDescription());
        $this->assertMatchesRegularExpression('/three/i', $strength->getStrengthDescription());
    }

    public function testThreeOfAKind()
    {
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 4)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Spade', 2));
        $deck->add(new Card('Spade', 10));
        $deck->add(new Card('Heart', 5));
        $deck->add(new Card('Diamond', 2));
        $deck->add(new Card('Heart', 11));

        $strength = new HandStrength($hand, $deck);
        $this->assertTrue(max($strength->getStrength()) == 302);
        $this->assertMatchesRegularExpression('/three of a kind/i', $strength->getStrengthDescription());
        $this->assertMatchesRegularExpression('/two/i', $strength->getStrengthDescription());
    }

    public function testSimpleStraight()
    {
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 3)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Spade', 4));
        $deck->add(new Card('Spade', 5));
        $deck->add(new Card('Heart', 6));
        $deck->add(new Card('Diamond', 2));
        $deck->add(new Card('Heart', 11));

        $strength = new HandStrength($hand, $deck);
        $this->assertTrue(max($strength->getStrength()) == 402);
        $this->assertMatchesRegularExpression('/straight/i', $strength->getStrengthDescription());
        $this->assertMatchesRegularExpression('/two/i', $strength->getStrengthDescription());
    }

//    public function testStraightFromAce()
//    {
//        // TODO: add this method

//    }

    public function testFlush()
    {
        // TODO: add logic to compare values by highest value by desc
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 6)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Spade', 12));
        $deck->add(new Card('Club', 5));
        $deck->add(new Card('Heart', 6));
        $deck->add(new Card('Club', 9));
        $deck->add(new Card('Club', 11));

        $strength = new HandStrength($hand, $deck);
        $this->assertTrue(max($strength->getStrength()) == 511);
        $this->assertMatchesRegularExpression('/flush/i', $strength->getStrengthDescription());
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
        $this->assertTrue(max($strength->getStrength()) == 710);
        $this->assertMatchesRegularExpression('/four of a kind/i', $strength->getStrengthDescription());
        $this->assertMatchesRegularExpression('/ten/i', $strength->getStrengthDescription());
    }

    public function testFullHouse()
    {
        $hand = new Hand([
            new Card('Club', 2),
            new Card('Club', 3)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Heart', 2));
        $deck->add(new Card('Spade', 3));
        $deck->add(new Card('Heart', 3));
        $deck->add(new Card('Diamond', 10));
        $deck->add(new Card('Heart', 11));

        $strength = new HandStrength($hand, $deck);
        $this->assertTrue(max($strength->getStrength()) == 603);
        $this->assertMatchesRegularExpression('/full house/i', $strength->getStrengthDescription());
    }

    public function testStraightFlush()
    {
        $hand = new Hand([
            new Card('Club', 4),
            new Card('Club', 5)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Club', 6));
        $deck->add(new Card('Club', 7));
        $deck->add(new Card('Club', 8));
        $deck->add(new Card('Diamond', 2));
        $deck->add(new Card('Heart', 11));

        $strength = new HandStrength($hand, $deck);
        $this->assertTrue(max($strength->getStrength()) == 804);
        $this->assertMatchesRegularExpression('/straight flush/i', $strength->getStrengthDescription());
    }

    public function testRoyalFlush()
    {
        $hand = new Hand([
            new Card('Club', 10),
            new Card('Club', 11)
        ]);
        $deck = new Deck();
        $deck->add(new Card('Club', 12));
        $deck->add(new Card('Club', 13));
        $deck->add(new Card('Club', 14));
        $deck->add(new Card('Diamond', 2));
        $deck->add(new Card('Heart', 11));

        $strength = new HandStrength($hand, $deck);
        $this->assertTrue(max($strength->getStrength()) == 900);
        $this->assertMatchesRegularExpression('/flush/i', $strength->getStrengthDescription());
        $this->assertMatchesRegularExpression('/royal/i', $strength->getStrengthDescription());
    }
}
