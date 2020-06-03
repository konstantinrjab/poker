<?php

namespace App\Models;

use App\Collections\PlayerCollection;

class Round
{
    private int $maxBet;
    private PlayerCollection $players;
    private array $bets;

    public function __construct(PlayerCollection $players)
    {
        $this->players = $players;
        $this->players->setActivePlayer($this->players->getBigBlind()->getId());
        $this->players->setNextActivePlayer();
    }

    public function getPlayerBet(string $playerId): int
    {
        return isset($this->bets[$playerId]) ? $this->bets[$playerId] : 0;
    }

    public function getMaxBet(): int
    {
        return $this->maxBet;
    }

    public function bet(string $playerId, int $amount): void
    {
        if (!isset($this->maxBet)) {
            $this->maxBet = $amount;
        }
        if ($amount > $this->maxBet) {
            $this->maxBet = $amount;
        }
        $this->bets[$playerId] = isset($this->bets[$playerId]) ? $this->bets[$playerId] + $amount : $amount;
        $this->players->getById($playerId)->pay($amount);
    }

    public function getPot(): int
    {
        $total = 0;
        foreach ($this->bets as $bet) {
            $total += $bet;
        }
        return $total;
    }

    public function shouldEnd(): bool
    {
        if (!isset($this->bets)) {
            return false;
        }

        foreach ($this->players as $player) {
            if ($player->getIsFolded()) {
                continue;
            }
            if (!isset($this->bets[$player->getId()]) || $this->bets[$player->getId()] != $this->maxBet) {
                return false;
            }
        }
        return true;
    }

    public function getAvailableActions(Player $player): array
    {
        //type: PlayerActionType;
        //options?: {min?: number; max?: number; value?: boolean};

        // TODO: finish this
        if (!isset($this->maxBet)) {
            return [];
        }
        if ($player->getIsFolded()) {
            return [];
        }
        $actions = [];
        if ($player->getMoney() >= $this->maxBet) {
            $actions[] = 'bet';
        }
        if ($this->getPlayerBet($player->getId()) == $this->maxBet) {
            $actions[] = 'check';
        }

        return $actions;
    }
}
