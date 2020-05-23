<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Collections\Deck;

class Round
{
    private const STATUS_PREFLOP = 1;
    private const STATUS_FLOP = 2;
    private const STATUS_TURN = 3;
    private const STATUS_RIVER = 4;
    private const STATUS_END = 4;
    private const STATUSES = [
        self::STATUS_PREFLOP,
        self::STATUS_FLOP,
        self::STATUS_TURN,
        self::STATUS_RIVER,
        self::STATUS_END,
    ];
    private const TABLE_CARDS_COUNT = 5;

    private Deck $deck;
    private PlayerCollection $players;
    private ?PlayerCollection $winners;
    private int $status;

    public function __construct(
        PlayerCollection $playerCollection,
        bool $newGame = true
    ) {
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

    public function getWinners(): ?PlayerCollection
    {
        return isset($this->winners) ? $this->winners : null;
    }

    public function passTurn(): void
    {
        $this->players->setNextActivePlayer();
    }

    public function shouldEnd(): bool
    {
//        return 1;
        // TODO: finish this logic
        return false && $this->status == self::TABLE_CARDS_COUNT;
    }

    public function end(): void
    {
        $this->status = self::STATUS_END;
        $this->calculateWinners();
    }

    public function getStatus(): int
    {
        return $this->status;
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
