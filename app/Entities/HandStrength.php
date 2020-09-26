<?php

namespace App\Entities;

use App\Entities\Collections\Deck;
use App\Entities\Collections\Hand;

class HandStrength
{
    private const PAIR_BASE = 100;
    private const TWO_PAIRS_BASE = 200;
    private const THREE_OF_A_KIND_BASE = 300;
    private const STRAIGHT_BASE = 400;
    private const FLUSH_BASE = 500;
    private const FULL_HOUSE_BASE = 600;
    private const FOUR_OF_A_KIND_BASE = 700;
    private const STRAIGHT_FLUSH_BASE = 800;
    private const FLUSH_ROYAL_BASE = 900;

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
        if ($this->strength) {
            return $this->strength;
        }

        $this->checkHeightCard();
        $this->checkPair();
        $this->checkTwoPair();
        $this->checkThreeOfAKind();
        $this->checkStraight();
        $this->checkFlush();
        $this->checkFullHouse();
        $this->checkFourOfAKind();
        $this->checkStraightFlush();
        $this->checkRoyalFlush();

        rsort($this->strength);

        return $this->strength;
    }

    public function getStrengthDescription(): string
    {
        // TODO: return value of cards
        $strength = $this->getStrength();
        $maxStr = $strength[0];
        $highestCard = $maxStr % 100;
        switch ($maxStr) {
            case $maxStr < self::PAIR_BASE:
                return 'Height card: ' . self::valueToDescription($maxStr);
            case $maxStr >= self::PAIR_BASE && $maxStr < self::TWO_PAIRS_BASE:
                return 'Pair of ' . self::valueToDescription($highestCard);
            case $maxStr >= self::TWO_PAIRS_BASE && $maxStr < self::THREE_OF_A_KIND_BASE:
                return 'Two pairs: ' . self::valueToDescription($highestCard) . ' and ' . self::valueToDescription($strength[1] % 100);
            case $maxStr >= self::THREE_OF_A_KIND_BASE && $maxStr < self::STRAIGHT_BASE:
                return 'Three of a kind: ' . self::valueToDescription($highestCard);
            case $maxStr >= self::STRAIGHT_BASE && $maxStr < self::FLUSH_BASE:
                return 'Straight from: ' . self::valueToDescription($highestCard);
            // TODO: add description for suite
            case $maxStr >= self::FLUSH_BASE && $maxStr < self::FULL_HOUSE_BASE:
                return 'Flush';
            // TODO: add description for few values
            case $maxStr >= self::FULL_HOUSE_BASE && $maxStr < self::FOUR_OF_A_KIND_BASE:
                return 'Full house';
            case $maxStr >= self::FOUR_OF_A_KIND_BASE && $maxStr < self::STRAIGHT_FLUSH_BASE:
                return 'Four of a kind: ' . self::valueToDescription($highestCard);
            case $maxStr >= self::STRAIGHT_FLUSH_BASE && $maxStr < self::FLUSH_ROYAL_BASE:
                return 'Straight flush for: ' . self::valueToDescription($highestCard);
            case $maxStr >= self::FLUSH_ROYAL_BASE:
                return 'Flush royal';
        }
        throw new \Exception('Cannot determine strength description');
    }

    public function checkHeightCard(): void
    {
        // TODO: figure out the rules and fix it. it doesn't work in a right way
        foreach ($this->hand as $card) {
            $this->strength[] = $card->getValue();
        }
    }

    private static function valueToDescription(int $value): string
    {
        return Card::VALUES[$value];
    }

    private function checkPair(): void
    {
        $baseStrength = self::PAIR_BASE;
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
        $baseStrength = self::TWO_PAIRS_BASE;
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
        $baseStrength = self::THREE_OF_A_KIND_BASE;
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
        $baseStrength = self::STRAIGHT_BASE;

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
        $baseStrength = self::FLUSH_BASE;
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
        $baseStrength = self::FULL_HOUSE_BASE;
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
        $baseStrength = self::FOUR_OF_A_KIND_BASE;
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
        $baseStrength = self::STRAIGHT_FLUSH_BASE;

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
        $deck = $this->mergedDeck->sortBy(function (Card $card): int {
            return $card->getValue();
        });

        foreach (Card::SUITS as $suit) {
            $cardsWithSuite = $deck->filter(function (Card $card) use ($suit): bool {
                return $card->getSuit() == $suit;
            });
            if ($cardsWithSuite->count() >= 5) {
                $suiteToTest = $cardsWithSuite->first()->getSuit();
                break;
            }
        }
        if (!isset($suiteToTest)) {
            return;
        }

        $values = [10, 11, 12, 13, 14];
        foreach ($values as $value) {
            /** @var Card $cardWithValue */
            $cardWithValue = $deck->first(function (Card $card) use ($value, $suiteToTest): bool {
                return $card->getValue() == $value && $card->getSuit() == $suiteToTest;
            });
            if (!$cardWithValue) {
                return;
            }
        }
        $this->strength[] = self::FLUSH_ROYAL_BASE;
    }
}
