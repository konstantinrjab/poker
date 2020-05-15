<?php

namespace App\Models;

class Player
{
    private string $id;
    private int $money;
    private Hand $hand;
    private int $strength;
    private string $name;
    private int $bet;
    private bool $isReadyToStart;
    private bool $isFolded;
//    private AvailableActionCollection $availableActions;

//'setReady' |
//'setNotReady' |
//'check' |
//'call' |
//'bet' |
//'raise' |
//'fold';

//id: string;
//type: PlayerActionType;
//options?: {min?: number; max?: number; value?: boolean};

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
