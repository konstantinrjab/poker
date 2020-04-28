<?php

namespace app\Models;

use app\Collections\PlayersCollection;

class Game
{
    private Round $round;
    private State $state;
    private PlayersCollection $players;

    public function addPlayer(Player $player): void
    {
        $this->players->add($player);
    }

    public function getPlayers(): PlayersCollection
    {
        return $this->players;
    }

    public function isReadyToStart(): bool
    {
        return true;
//        return count($this->players) == 5;
    }

    public function start(): void
    {
        $this->round = new Round();
        $this->round->addPlayers($this->players);
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
