<?php

namespace App\Entities;

use App\Entities\Collections\Hand;
use Exception;

class Player
{
    private string $id;
    private string $name;
    private int $money;
    private ?Hand $hand;
    private array $strength = [];
    private bool $isReady = false;
    private bool $isFolded = false;

    public function __construct(string $id, string $name, int $money)
    {
        $this->id = $id;
        $this->name = $name;
        $this->money = $money;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIsReady(): bool
    {
        return $this->isReady;
    }

    public function setIsReady(bool $isReady): void
    {
        $this->isReady = $isReady;
    }

    public function getHand(): ?Hand
    {
        return isset($this->hand) ? $this->hand : null;
    }

    public function setHand(Hand $hand): void
    {
        $this->hand = $hand;
    }

    public function getStrength(): array
    {
        return $this->strength;
    }

    public function setStrength(array $strength): void
    {
        $this->strength = $strength;
    }

    public function getMoney(): int
    {
        return $this->money;
    }

    public function pay(int $amount): void
    {
        if ($amount > $this->money) {
            throw new Exception("Player {$this->id} doesn't have enough money");
        }
        $this->money -= $amount;
    }

    public function earn(int $amount): void
    {
        $this->money += $amount;
    }

    public function getIsFolded(): bool
    {
        return $this->isFolded;
    }

    public function fold(): void
    {
        $this->isFolded = true;
    }
}
