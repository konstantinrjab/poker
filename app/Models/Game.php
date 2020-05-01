<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use Illuminate\Support\Str;

class Game
{
    private int $creatorId;
    private string $id;
    private Round $round;
    private State $state;
    private PlayerCollection $playerCollection;

    public function __construct(int $creatorId)
    {
        $this->creatorId = $creatorId;
        $this->playerCollection = new PlayerCollection();
        $this->id = Str::uuid();
        $this->state = new State();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function addPlayer(Player $player): void
    {
        $this->playerCollection->add($player);
    }

    public function getPlayers(): PlayerCollection
    {
        return $this->playerCollection;
    }

    public function isReadyToStart(): bool
    {
        return true;
//        return count($this->players) == 5;
    }

    public function start(): void
    {
        $this->round = new Round($this->playerCollection);
        $this->state->setStatus(State::STATUS_STARTED);
    }

    public function end(): void
    {
        $this->state->setStatus(State::STATUS_END);
    }

    public function getRound(): Round
    {
        return $this->round;
    }
}
