<?php

namespace app\Models;

use Illuminate\Support\Str;

class Player
{
    private string $id;
    private int $userId;
    private int $money;
    private Deck $deck;
    private int $strength;

    public function __construct(int $userId)
    {
        $this->id = Str::uuid();
        $this->userId = $userId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function makeBid(int $amount): void
    {
        $this->money -= $amount;
    }

    public function getDeck(): Deck
    {
        return $this->deck;
    }

    public function setStrength(int $strength): void
    {
        $this->strength = $strength;
    }
}
