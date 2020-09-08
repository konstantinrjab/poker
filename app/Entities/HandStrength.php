<?php

namespace App\Entities;

use App\Entities\Collections\Deck;
use App\Entities\Collections\Hand;

class HandStrength
{
    private array $strength = [];

    private Hand $hand;
    private Deck $dealDeck;
    private Deck $mergedDeck;

    public function __construct(Hand $hand, Deck $deck)
    {
        $this->hand = $hand;
        $this->dealDeck = $deck;
        $this->mergedDeck = $deck->merge($hand);
    }

    public function getStrength(): array
    {
        $this->checkHeightCard();
        $this->checkPair();
        $this->checkTwoPair();
        $this->checkThreeOfAKind();
        $this->checkStraight();
        $this->checkFlush();
        $this->checkFullHouse();
        $this->checkFourOfAKind();
        $this->checkStraightFlush();

//        $this->checkRoyalFlush();

        rsort($this->strength);

        return $this->strength;
    }

    public function checkHeightCard(): void
    {
        // TODO: figure out the rules and fix it. it doesn't work in a right way
        foreach ($this->hand as $card) {
            $this->strength[] = $card->getValue();
        }
    }

    private function checkPair(): void
    {
        $baseStrength = 100;
        $hasValues = [];

        foreach ($this->mergedDeck as $card) {
            $pairStrength = $baseStrength + $card->getValue();
            if (!empty($hasValues[$card->getValue()])) {
                $this->strength[] = $pairStrength;
            }
            $hasValues[$card->getValue()] = true;
        }
    }

    private function checkTwoPair(): void
    {
        $baseStrength = 200;
        $countsByValues = [];

        foreach ($this->mergedDeck as $card) {
            if (!isset($countsByValues[$card->getValue()])) {
                $countsByValues[$card->getValue()] = 1;
            } else {
                $countsByValues[$card->getValue()]++;
            }
        }
        $pairsByValues = [];
        foreach ($countsByValues as $value => $count) {
            if ($count >= 2) {
                $pairsByValues[] = $value;
            }
        }
        if (count($pairsByValues) < 2) {
            return;
        }

        foreach ($pairsByValues as $value) {
            $pairStrength = $baseStrength + $value;
            $this->strength[] = $pairStrength;
        }
    }

    private function checkThreeOfAKind(): void
    {
        $baseStrength = 300;
        $countsByValues = [];

        foreach ($this->mergedDeck as $card) {
            if (!isset($countsByValues[$card->getValue()])) {
                $countsByValues[$card->getValue()] = 1;
            } else {
                $countsByValues[$card->getValue()]++;
            }
        }
        foreach ($countsByValues as $value => $count) {
            $combinationStrength = $baseStrength + $value;
            if ($count >= 3) {
                $this->strength[] = $combinationStrength;
            }
        }
    }

    private function checkStraight(): void
    {
        $baseStrength = 400;

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
                $cardWithNextValue = $deck->first(function (Card $card) use ($value): bool {
                    return $card->getValue() == $value;
                });
                if ($cardWithNextValue) {
                    $consecutiveCount++;
                } else {
                    $consecutiveCount = 1;
                    break;
                }
            }
            if ($consecutiveCount == 5) {
                $this->strength[] = $baseStrength + $card->getValue();
            }
        }
    }

    public function checkFlush(): void
    {
        $baseStrength = 500;
        $cardsBySuits = [];

        $deck = $this->mergedDeck->sortByDesc(function (Card $card): int {
            return $card->getValue();
        });
        foreach ($deck as $card) {
            /** @var Card $card */
            $cardsBySuits[$card->getSuit()] = isset($cardsBySuits[$card->getSuit()]) ? $cardsBySuits[$card->getSuit()] + 1 : 1;
        }
        foreach ($cardsBySuits as $suit => $cardsBySuit) {
            if ($cardsBySuit == 5) {
                // TODO: add logic to compare values by highest value by desc
                $highestValueWithSuit = $deck->first(function (Card $card) use ($suit): bool {
                    return $card->getSuit() == $suit;
                });
                $this->strength[] = $baseStrength + $highestValueWithSuit->getValue();
            }
        }
    }

    public function checkFullHouse(): void
    {
        $baseStrength = 600;
        $countsByValues = [];

        foreach ($this->mergedDeck as $card) {
            if (!isset($countsByValues[$card->getValue()])) {
                $countsByValues[$card->getValue()] = 1;
            } else {
                $countsByValues[$card->getValue()]++;
            }
        }
        $firstPart = false;
        $nextCount = 7;
        foreach ($countsByValues as $value => $count) {
            if ($count >= $nextCount && $firstPart) {
                // TODO: add logic for different values in combination
                $combinationStrength = $baseStrength + $value;
                $this->strength[] = $combinationStrength;
            }
            if ($count == 2) {
                $nextCount = 3;
                $firstPart = true;
            } elseif ($count >= 3) {
                $nextCount = 2;
                $firstPart = true;
            }
        }
    }

    private function checkFourOfAKind(): void
    {
        $baseStrength = 700;
        $countsByValues = [];

        foreach ($this->mergedDeck as $card) {
            if (!isset($countsByValues[$card->getValue()])) {
                $countsByValues[$card->getValue()] = 1;
            } else {
                $countsByValues[$card->getValue()]++;
            }
        }
        foreach ($countsByValues as $value => $count) {
            $combinationStrength = $baseStrength + $value;
            if ($count >= 4) {
                $this->strength[] = $combinationStrength;
            }
        }
    }

    public function checkStraightFlush(): void
    {
        $baseStrength = 800;

        // TODO: add starts from ace logic
        $consecutiveCount = 1;

        $deck = $this->mergedDeck->sortBy(function (Card $card): int {
            return $card->getValue();
        });

        foreach ($deck as $card) {
            $suit = $card->getSuit();
            $consecutiveValues = [
                $card->getValue() + 1,
                $card->getValue() + 2,
                $card->getValue() + 3,
                $card->getValue() + 4,
            ];
            foreach ($consecutiveValues as $value) {
                $nextCard = $deck->first(function (Card $card) use ($suit, $value): bool {
                    return $card->getValue() == $value && $card->getSuit() == $suit;
                });
                if ($nextCard) {
                    $consecutiveCount++;
                } else {
                    $consecutiveCount = 1;
                    break;
                }
            }
            if ($consecutiveCount == 5) {
                $this->strength[] = $baseStrength + $card->getValue();
            }
        }
    }

    public function checkRoyalFlush(): void
    {
        $base = 900;
        return;
    }
}
