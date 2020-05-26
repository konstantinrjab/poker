<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Collections\Deck;

class Deal
{
    private const STATUS_PREFLOP = 1;
    private const STATUS_FLOP = 2;
    private const STATUS_TURN = 3;
    private const STATUS_RIVER = 4;
    private const STATUS_END = 5;
    private const TABLE_CARDS_COUNT = 5;

    private Round $round;
    private Deck $deck;
    private PlayerCollection $players;
    private ?PlayerCollection $winners;
    private int $status;
    private int $pot;

    public function __construct(
        PlayerCollection $playerCollection,
        GameConfig $config,
        bool $newGame
    )
    {
        $this->round = new Round($playerCollection);
        $deck = Deck::getFull();
        $this->players = $playerCollection;
        foreach ($this->players as $player) {
            $player->setHand($deck->getHand());
        }
        $this->deck = $deck->take(self::TABLE_CARDS_COUNT);
        $this->status = self::STATUS_PREFLOP;
        if (!$newGame) {
            $this->players->setNextBigBlind();
            $this->players->setNextSmallBlind();
            $this->players->setNextDealer();
        }
        $this->players->getSmallBlind()->pay($config->getSmallBlind());
        $this->players->getBigBlind()->pay($config->getBigBlind());
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getPlayers(): PlayerCollection
    {
        return $this->players;
    }

    public function getRound(): Round
    {
        return $this->round;
    }

    public function getPot(): ?int
    {
        return isset($this->pot) ? $this->pot : null;
    }

    public function addToPot(int $amount): void
    {
        $this->pot += $amount;
    }

    public function getWinners(): ?PlayerCollection
    {
        return isset($this->winners) ? $this->winners : null;
    }

    public function onAfterUpdate(): void
    {
        if ($this->shouldEnd()) {
            $this->end();
            return;
        }
        if ($this->status != self::STATUS_RIVER && $this->round->shouldEnd()) {
            $this->round = new Round();
        }
        $this->passTurn();
    }

    private function shouldEnd(): bool
    {
        return $this->round->shouldEnd() && $this->status == self::STATUS_RIVER;
    }

    private function end(): void
    {
        $this->status = self::STATUS_END;
        $this->calculateWinners();
    }

    private function passTurn(): void
    {
        $this->players->setNextActivePlayer();
        $this->players->setNextBigBlind();
        $this->players->setNextSmallBlind();
        $this->players->setNextDealer();
    }

    private function calculateWinners(): void
    {
        foreach ($this->players as $player) {
            $playerStrengthDeck = new HandStrength($player->getHand(), $this->deck);
            $player->setStrength($playerStrengthDeck->getStrength());
        }
        $maxStrength = $this->players->max(function (Player $player): int {
            return $player->getStrength();
        });
        $sameStrength = $this->players->filter(function (Player $player) use ($maxStrength): bool {
            return $player->getStrength() == $maxStrength;
        });
        if ($sameStrength->count() == 1) {
            $this->winners = $sameStrength;
            return;
        }
        // TODO: finish logic using players heightCards
        $this->winners = $sameStrength;
    }
}
