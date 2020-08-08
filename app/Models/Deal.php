<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Collections\Deck;

class Deal
{
    public const STATUS_PREFLOP = 1;
    public const STATUS_FLOP = 2;
    public const STATUS_TURN = 3;
    public const STATUS_RIVER = 4;
    public const STATUS_END = 5;
    public const TABLE_CARDS_COUNT = 5;

    private Round $round;
    private Deck $deck;
    private PlayerCollection $players;
    private ?PlayerCollection $winners;
    private int $status;
    private int $pot;
    private GameConfig $config;

    public function __construct(
        PlayerCollection $playerCollection,
        GameConfig $config,
        bool $newGame
    )
    {
        $this->config = $config;
        $this->round = new Round($playerCollection, $config);
        $this->round->initBlinds();

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
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getRound(): Round
    {
        return $this->round;
    }

    public function getPot(): int
    {
        return isset($this->pot) ? $this->pot + $this->round->getPot() : $this->round->getPot();
    }

    public function getWinners(): ?PlayerCollection
    {
        return isset($this->winners) ? $this->winners : null;
    }

    public function showCards(): Deck
    {
        if ($this->status == self::STATUS_PREFLOP) {
            $limit = 0;
        } else if ($this->status == self::STATUS_FLOP) {
            $limit = 3;
        } else if ($this->status == self::STATUS_TURN) {
            $limit = 4;
        } else if ($this->status == self::STATUS_RIVER) {
            $limit = 5;
        }
        return $this->deck->take($limit);
    }

    public function onAfterUpdate(): void
    {
        if ($this->round->shouldEnd() && $this->status == self::STATUS_RIVER) {
            $this->end();
            return;
        } else if ($this->round->shouldEnd() && $this->status != self::STATUS_RIVER) {
            $this->pot = isset($this->pot) ? $this->pot + $this->round->getPot() : $this->round->getPot();
            $this->round = new Round($this->players, $this->config);
            $this->updateStatus();
            return;
        }
        $this->players->setNextActivePlayer();
    }

    private function end(): void
    {
        $this->calculateWinners();
        $this->splitPot();
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

    private function splitPot(): void
    {
        $amount = $this->pot / $this->winners->count();
        foreach ($this->winners as $winner) {
            $winner->earn($amount);
        }
    }

    private function updateStatus(): void
    {
        if ($this->status == self::STATUS_PREFLOP) {
            $this->status = self::STATUS_FLOP;
        } else if ($this->status == self::STATUS_FLOP) {
            $this->status = self::STATUS_TURN;
        } else if ($this->status == self::STATUS_TURN) {
            $this->status = self::STATUS_RIVER;
        } else if ($this->status == self::STATUS_RIVER) {
            $this->status = self::STATUS_END;
        }
    }
}
