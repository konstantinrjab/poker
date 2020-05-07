<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Collections\Deck;

class Round
{
    private const TABLE_CARDS_COUNT = 5;
    private const STATUS_PREFLOP = 1;
    private const STATUS_FLOP = 2;
    private const STATUS_TURN = 3;
    private const STATUS_RIVER = 4;
    private const STATUSES = [
        self::STATUS_PREFLOP,
        self::STATUS_FLOP,
        self::STATUS_TURN,
        self::STATUS_RIVER,
    ];

    private Deck $deck;
    private PlayerCollection $playerCollection;
    private int $status;

    public function __construct(PlayerCollection $playerCollection)
    {
        $deck = Deck::getFull();
        $this->playerCollection = $playerCollection;
        foreach ($this->playerCollection as $player) {
            $player->setHand($deck->getHand());
        }
        $this->deck = $deck->take(self::TABLE_CARDS_COUNT);
        $this->status = self::STATUS_PREFLOP;
    }

    public function getWinners(): PlayerCollection
    {
        foreach ($this->playerCollection as $player) {
            $playerStrengthDeck = new HandStrength($player->getHand(), $this->deck);
            $player->setStrength($playerStrengthDeck->getStrength());
        }
        $maxStrength = $this->playerCollection->max(function (Player $player): int {
            return $player->getStrength();
        });
        $winners = $this->playerCollection->filter(function (Player $player) use ($maxStrength): bool {
            return $player->getStrength() == $maxStrength;
        });
        return $winners;
    }

    public function getActivePlayer(): Player
    {
        return $this->playerCollection->getActivePlayer();
    }

    public function passTurn(): void
    {
        $this->playerCollection->setNextActivePlayer();
    }
}
