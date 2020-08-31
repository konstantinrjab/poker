<?php

namespace App\Entities;

use App\Exceptions\GameException;

class Card
{
    public const SUITS = [
        self::CLUB,
        self::SPADE,
        self::DIAMOND,
        self::HEART
    ];
    public const VALUES = [
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Jack',
        12 => 'Queen',
        13 => 'King',
        14 => 'Ace'
    ];

    private const CLUB = 'Club';
    private const SPADE = 'Spade';
    private const DIAMOND = 'Diamond';
    private const HEART = 'Heart';

    private int $value;
    private string $suit;

    public function __construct(string $suit, int $value)
    {
        $this->setSuit($suit);
        $this->setValue($value);
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getSuit(): string
    {
        return $this->suit;
    }

    public function setValue($value): void
    {
        if (!$this->isValidValue($value)) {
            throw new GameException("An invalid value was set for a card: $value");
        }

        $this->value = $value;
    }

    public function setSuit($suit): void
    {
        if (!$this->isValidSuit($suit)) {
            throw new GameException("An invalid suit was set for a card: $suit");
        }

        $this->suit = $suit;
    }

    private function isValidValue($value): bool
    {
        return array_key_exists($value, self::VALUES);
    }

    private function isValidSuit($suit): bool
    {
        return in_array($suit, self::SUITS);
    }
}
