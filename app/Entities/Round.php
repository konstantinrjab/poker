<?php

namespace App\Entities;

use App\Entities\Collections\PlayerCollection;
use App\Entities\Actions\Action;
use App\Entities\Actions\BetAction;
use App\Entities\Actions\CallAction;
use App\Entities\Actions\CheckAction;
use App\Entities\Actions\FoldAction;

class Round
{
    private int $maxBet = 0;
    private GameConfig $config;
    private PlayerCollection $players;
    private array $bets;

    public function __construct(PlayerCollection $players, GameConfig $config, bool $isPreFlop)
    {
        $this->players = $players;
        if ($isPreFlop) {
            $playerId = $this->players->getBigBlind()->getId();
        } else {
            $playerId = $this->players->getDealer()->getId();
        }
        $this->players->setActivePlayer($playerId);
        $this->players->setNextActivePlayer();
        $this->config = $config;
    }

    public function initBlinds(): void
    {
        $bigBlindId = $this->players->getBigBlind()->getId();
        $smallBlindId = $this->players->getSmallBlind()->getId();

        $this->bet($smallBlindId, $this->config->getSmallBlind());
        $this->bet($bigBlindId, $this->config->getBigBlind());
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
        if (!$this->maxBet) {
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
        if (!isset($this->bets)) {
            return $total;
        }
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

    public function isOnlyOnePlayerNotFolded(): bool
    {
        $notFoldedCount = 0;
        foreach ($this->players as $player) {
            if (!$player->getIsFolded()) {
                $notFoldedCount++;
            }
        }

        return $notFoldedCount === 1;
    }

    /**
     * @param Player $player
     * @return Action[]
     */
    public function getAvailableActions(Player $player): array
    {
        if ($player->getIsFolded()) {
            return [];
        }
        $actions[] = new FoldAction();

        $betEqualsMaxBet = $this->getPlayerBet($player->getId()) == $this->maxBet;
        if ($betEqualsMaxBet) {
            $actions[] = new CheckAction();
        }

        $amountToCall = $this->maxBet - $this->getPlayerBet($player->getId());

        // TODO: test with all in
        if ($player->getMoney() >= ($amountToCall + $this->config->getBigBlind())) {
            $actions[] = new BetAction();
        }

        if (!$betEqualsMaxBet && $player->getMoney() >= $amountToCall) {
            $actions[] = new CallAction();
        }

        return $actions;
    }
}
