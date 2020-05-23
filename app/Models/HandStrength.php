<?php

namespace App\Models;

use App\Collections\Deck;

class HandStrength
{
    private int $strength = 0;
    private Card $kicker;
    private $highEnd;
    private $flushSuit;

    private Hand $hand;
    private Deck $roundDeck;
    private Deck $mergedDeck;


    private const HANDS = [
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

    public function __construct(Hand $hand, Deck $roundDeck)
    {
        $this->hand = $hand;
        $this->roundDeck = $roundDeck;
        $this->mergedDeck = $roundDeck->merge($hand);
    }

    public function getStrength(): int
    {
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

//        if ($this->isFullHouse()) {
//            $this->strength = 6;
//        }

        if ($this->isFourOfAKind()) {
            $this->strength = 7;
        }

//        if ($this->isStraightFlush()) {
//            $this->strength = 8;
//        }
//
//        if ($this->isRoyalFlush()) {
//            $this->strength = 9;
//        }
        return $this->strength;
    }

    public function getHeightCard(): int
    {
        return $this->mergedDeck->max('value');
    }

    private function isPair(): bool
    {
        $countsByValues = [];

        foreach ($this->mergedDeck as $card) {
            if (!empty($countsByValues[$card->getValue()])) {
                return true;
            }
            $countsByValues[$card->getValue()] = true;
        }

        return false;
    }

    private function isTwoPair(): bool
    {
        $countsByValues = [];
        $pairsCount = 0;

        foreach ($this->mergedDeck as $card) {
            if (!isset($countsByValues[$card->getValue()])) {
                $countsByValues[$card->getValue()] = 0;
            }
            $countsByValues[$card->getValue()]++;
        }
        foreach ($countsByValues as $value) {
            if ($value >= 2) {
                $pairsCount++;
            }
        }
        return $pairsCount >= 2;
    }

    private function isThreeOfAKind(): bool
    {
        $countsByValues = [];

        foreach ($this->mergedDeck as $card) {
            if (!isset($countsByValues[$card->getValue()])) {
                $countsByValues[$card->getValue()] = 0;
            }
            $countsByValues[$card->getValue()]++;
        }
        foreach ($countsByValues as $value) {
            if ($value >= 3) {
                return true;
            }
        }
        return false;
    }

    private function isStraight(): bool
    {
        // TODO: add starts from ace logic
        $consecutiveCount = 1;

        $deck = $this->mergedDeck->sortBy(function (Card $card): int {
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
                if ($deck->firstWhere('value', $value)) {
                    $consecutiveCount++;
                } else {
                    $consecutiveCount = 0;
                }
            }
            if ($consecutiveCount == 5) {
                return true;
            }
        }
        return false;
    }

    private function isFourOfAKind(): bool
    {
        $countsByValues = [];

        foreach ($this->mergedDeck as $card) {
            if (!isset($countsByValues[$card->getValue()])) {
                $countsByValues[$card->getValue()] = 0;
            }
            $countsByValues[$card->getValue()]++;
        }
        foreach ($countsByValues as $value) {
            if ($value >= 4) {
                return true;
            }
        }
        return false;
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

    public function isRoyalFlush(): bool
    {
        return false;
    }

    public function isFullHouse()
    {
        return $this->isPair() && $this->isThreeOfAKind();
    }

    public function isFlush()
    {
        $cardsBySuits = [];

        foreach ($this->mergedDeck as $card) {
            /** @var Card $card */
            $cardsBySuits[$card->getSuit()] = isset($cardsBySuits[$card->getSuit()]) ? $cardsBySuits[$card->getSuit()] + 1 : 1;
        }
        foreach ($cardsBySuits as $cardsBySuit) {
            if (count($cardsBySuit) == 5) {
                return true;
            }
        }
        return false;
    }

    public function sortCards()
    {
        $this->sortByDesc(function ($card) {
            return $card->getValue();
        });
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
