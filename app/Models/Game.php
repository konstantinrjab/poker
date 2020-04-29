<?php

namespace App\Models;

use App\Collections\PlayerCollection;

class Game
{
    private Round $round;
    private State $state;
    private PlayerCollection $playerCollection;

    public function __construct()
    {
        $this->playerCollection = new PlayerCollection();
        $this->state = new State();
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
        $this->round = new Round();
        $this->round->addPlayers($this->playerCollection);
        $this->state->setStatus(State::STATUS_STARTED);
        $this->round->start();
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
