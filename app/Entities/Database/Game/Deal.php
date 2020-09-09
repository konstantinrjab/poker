<?php

namespace App\Entities\Database\Game;

use App\Entities\Collections\PlayerCollection;
use App\Entities\Collections\Deck;
use Exception;
use Facades\App\Entities\WinnerDetector;

class Deal
{
    public const STATUS_PREFLOP = 'preflop';
    public const STATUS_FLOP = 'flop';
    public const STATUS_TURN = 'turn';
    public const STATUS_RIVER = 'river';
    public const STATUS_END = 'end';
    public const TABLE_CARDS_COUNT = 5;

    private Round $round;
    private Deck $deck;
    private PlayerCollection $players;
    private ?PlayerCollection $winners;
    private string $status;
    private int $pot;
    private GameConfig $config;

    public function __construct(
        PlayerCollection $playerCollection,
        GameConfig $config,
        bool $isNewGame
    )
    {
        $this->config = $config;
        $this->round = new Round($playerCollection, $config, true);
        $this->round->initBlinds();

        $deck = Deck::getFull();
        $this->players = $playerCollection;
        foreach ($this->players as $player) {
            $player->setHand($deck->getHand());
        }
        $this->deck = $deck->take(self::TABLE_CARDS_COUNT);
        $this->status = self::STATUS_PREFLOP;
        if (!$isNewGame) {
            $this->players->moveDealer();
        }
    }

    public function getStatus(): string
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

    public function isNeedToShowCards(): bool
    {
        return $this->status != self::STATUS_END;
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
        } else {
            throw new Exception('Cannot show cards for deals status: ' . $this->status);
        }
        return $this->deck->take($limit);
    }

    public function onAfterUpdate(): void
    {
        $lastTurnReached = $this->round->shouldEnd() && $this->status == self::STATUS_RIVER;

        if ($lastTurnReached || $this->round->isOnlyOnePlayerNotFolded()) {
            $this->end();
        } else if ($this->round->shouldEnd() && $this->status != self::STATUS_RIVER) {
            $this->startNextRound();
        } else {
            $this->players->setNextActivePlayer();
        }
    }

    private function end(): void
    {
        $this->winners = WinnerDetector::detect($this->deck, $this->players);
        $this->splitPot();
        $this->updateStatus();
    }

    private function startNextRound(): void
    {
        $this->pot = isset($this->pot) ? $this->pot + $this->round->getPot() : $this->round->getPot();
        $this->round = new Round($this->players, $this->config, false);
        $this->updateStatus();
    }

    private function splitPot(): void
    {
        $amount = $this->getPot() / $this->winners->count();
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
