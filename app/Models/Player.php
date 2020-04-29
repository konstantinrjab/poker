<?php

namespace App\Models;

use Illuminate\Support\Str;

class Player
{
    private string $id;
    private int $userId;
    private int $money;
    private Hand $hand;
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

    public function getHand(): Hand
    {
        return $this->hand;
    }

    public function setStrength(int $strength): void
    {
        $this->strength = $strength;
    }
}
