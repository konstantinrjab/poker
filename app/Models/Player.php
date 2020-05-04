<?php

namespace App\Models;

class Player
{
    private string $id;
    private int $money;
    private Hand $hand;
    private int $strength;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function makeBid(int $amount): void
    {
        $this->money -= $amount;
    }

    public function setHand(Hand $hand): void
    {
        $this->hand = $hand;
    }

    public function getHand(): Hand
    {
        return $this->hand;
    }

    public function setStrength(int $strength): void
    {
        $this->strength = $strength;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }
}
