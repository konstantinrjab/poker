<?php

namespace app\Models;

class HandStrength extends Deck
{
    protected int $strength = 0;
    protected Card $kicker;
    protected $highEnd;
    protected $flushSuit;

    protected const HANDS = [
        'High Card',
        'Pair',
        'Two Pair',
        'Three of a Kind',
        'Straight',
        'Flush',
        'Full House',
        'Quads',
        'Straight Flush',
        'Royal Flush'
    ];

    public function getHeightCard(): int
    {
        return $this->max('value');
    }

    public function getStrength(): int
    {
        $this->sortCards();

        if ($this->isPair()) {
            $this->strength = 1;
        }

        if ($this->isTwoPair()) {
            $this->strength = 2;
        }

        if ($this->isThreeOfAKind()) {
            $this->strength = 3;
        }

        if ($this->isStraight()) {
            $this->strength = 4;
        }

        if ($this->isFlush()) {
            $this->strength = 5;
        }

        if ($this->isFullHouse()) {
            $this->strength = 6;
        }

        if ($this->isFourOfAKind()) {
            $this->strength = 7;
        }

        if ($this->isStraightFlush()) {
            $this->strength = 8;
        }

        if ($this->isRoyalFlush()) {
            $this->strength = 9;
        }
        return $this->strength;
    }

    public function isRoyalFlush()
    {

    }

    public function isStraightFlush()
    {
        if ($this->isFlush()) {
            $flushCards = [];
            foreach ($this->cards as $card) {
                if ($card->getSuit() == $this->flushSuit) {
                    $flushCards[] = clone $card;
                }
            }
            $handStrength = new HandStrength($flushCards);
            $handStrength->sortCards();
            if ($handStrength->isStraight()) {
                $this->highEnd = $handStrength->getHighEnd();
                return true;
            }
        }
        return false;
    }

    public function isFourOfAKind()
    {
        // We always want to flip aces to high aces
        // for any type of multiple card hand
        $this->flipAces();

        $valueTally = [];

        foreach ($this->cards as $card) {
            empty($valueTally[$card->getValue()]) ? $valueTally[$card->getValue()] = 1 : $valueTally[$card->getValue()]++;
        }

        foreach ($valueTally as $tally) {
            if ($tally === 4) {
                $this->flipAces();

                return true;
            }
        }

        $this->flipAces();

        return false;
    }

    public function isFullHouse()
    {
        return $this->isPair() && $this->isThreeOfAKind();
    }

    public function isFlush()
    {
        $suitTally = [];

        foreach ($this->cards as $card) {
            empty($suitTally[$card->getSuit()]) ? $suitTally[$card->getSuit()] = 1 : $suitTally[$card->getSuit()]++;
        }

        foreach ($suitTally as $suit => $tally) {
            if ($tally >= 5) {
                $this->flushSuit = $suit;
                return true;
            }
        }

        return false;
    }

    public function isStraight(): bool
    {
        $lowStraight = $this->findStraight();
        $highStraight = $this->findStraight();

        return $highStraight ?: $lowStraight;
    }

    public function sortCards()
    {
        $this->sortByDesc(function ($card) {
            return $card->getValue();
        });
    }

    public function findStraight(): bool
    {
        $consecutiveCount = 1;

        $deck = $this->sortBy(function (Card $card): int {
            return $card->getValue();
        });

        foreach ($deck as $card) {
            $consecutiveValues = [
                $card->getValue() + 1,
                $card->getValue() + 2,
                $card->getValue() + 3,
                $card->getValue() + 4,
            ];
            foreach ($consecutiveValues as $value) {
                $consecutive = $deck->filter(function (Card $card) use ($value): bool {
                    return $card->getValue() == $value;
                });
                if ($consecutive->count()) {
                    $consecutiveCount++;
                }
            }
            if ($consecutiveCount == 5) {
                return true;
            }
        }
        return false;
    }

    public function isThreeOfAKind(): bool
    {
        $values = [];

        foreach ($this->cards as $card) {
            $values[$card->getValue()] = $values[$card->getValue()] ?? 0;
            $values[$card->getValue()]++;
        }
        foreach ($values as $value) {
            if ($value >= 3) {
                return true;
            }
        }
        return false;
    }

    public function isTwoPair(): bool
    {
        $values = [];
        $pairsCount = 0;

        foreach ($this->cards as $card) {
            $values[$card->getValue()] = $values[$card->getValue()] ?? 0;
            $values[$card->getValue()]++;
        }
        foreach ($values as $value) {
            if ($value >= 2) {
                $pairsCount++;
            }
        }
        return $pairsCount >= 2;
    }

    public function isPair(): bool
    {
        $values = [];

        foreach ($this->cards as $card) {
            if (!empty($values[$card->getValue()])) {
                return true;
            }
            $values[$card->getValue()] = true;
        }

        return false;
    }

    public function getKicker()
    {
        return $this->kicker;
    }

    public function getKickerDescription()
    {
        if ($kicker = $this->getKicker()) {
            return ' ' . $this->kicker . ' Kicker';
        }

        if ($high = $this->getHighEndWord()) {
            return ' ' . $high . ' High';
        }
    }

    protected function getHighEnd()
    {
        return $this->highEnd;
    }

    public function getHandType()
    {
        $this->getStrength();
        return self::HANDS[$this->strength];
    }
}
