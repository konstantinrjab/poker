<?php

namespace app\Models;

use app\Exceptions\InvalidCardPropertyException;

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

    protected int $value;
    protected string $suit;

    public function __construct(string $suit, int $value)
    {
        $this->setSuit($suit);
        $this->setValue($value);
    }

    public function getDescription()
    {
        return self::VALUES[$this->getValue()] . ' of ' . $this->getSuit() . 's';
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
            throw new InvalidCardPropertyException("An invalid value was set for a card: $value");
        }

        $this->value = $value;
    }

    public function setSuit($suit): void
    {
        if (!$this->isValidSuit($suit)) {
            throw new InvalidCardPropertyException("An invalid suit was set for a card: $suit");
        }

        $this->suit = $suit;
    }

    protected function isValidValue($value): bool
    {
        return array_key_exists($value, self::VALUES);
    }

    protected function isValidSuit($suit): bool
    {
        return in_array($suit, self::SUITS);
    }
}
