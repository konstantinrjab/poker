<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Models\Actions\Abstracts\Action;
use App\Models\Actions\BetAction;
use App\Models\Actions\CallAction;
use App\Models\Actions\CheckAction;
use App\Models\Actions\FoldAction;

class Round
{
    private int $maxBet;
    private GameConfig $config;
    private PlayerCollection $players;
    private array $bets;

    public function __construct(PlayerCollection $players, GameConfig $config)
    {
        $this->players = $players;
        $this->players->setActivePlayer($this->players->getBigBlind()->getId());
        $this->players->setNextActivePlayer();
        $this->config = $config;
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
        $bigBlind = $this->config->getBigBlind();
        // case for raise without blind bet before
        if (!$this->getPlayerBet($playerId) && $amount > $bigBlind) {
            $raiseAmount = $amount - $bigBlind;
        } else {
            // case for big blind
            $raiseAmount = $amount;
        }
        if ($raiseAmount > $this->maxBet) {
            $this->maxBet = $raiseAmount;
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

    /**
     * @param Player $player
     * @return Action[]
     */
    public function getAvailableActions(Player $player): array
    {
        if (!isset($this->maxBet)) {
            return [];
        }
        if ($player->getIsFolded()) {
            return [];
        }
        $actions[] = new FoldAction();

        if ($this->getPlayerBet($player->getId()) == $this->maxBet) {
            $actions[] = new CheckAction();
        }

        $amountToCall = $this->maxBet - $this->getPlayerBet($player->getId());

        // TODO: test with all in
        if ($player->getMoney() >= ($amountToCall + $this->config->getBigBlind())) {
            $actions[] = new BetAction();
        }

        if ($player->getMoney() >= $amountToCall) {
            $actions[] = new CallAction();
        }

        return $actions;
    }

    public function initBlinds(): void
    {
        $bigBlindId = $this->players->getBigBlind()->getId();
        $smallBlindId = $this->players->getSmallBlind()->getId();

        $this->bet($smallBlindId, $this->config->getSmallBlind());
        $this->bet($bigBlindId, $this->config->getBigBlind());
    }
}
