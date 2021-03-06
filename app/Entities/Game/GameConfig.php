<?php

namespace App\Entities\Game;

class GameConfig
{
    private int $bigBlind;
    private int $smallBlind;
    private int $initialMoney;
    private int $maxPlayers;
    private int $minPlayers;
    private ?int $timeout;

    public function __construct(
        int $smallBlind,
        int $bigBlind,
        int $initialMoney,
        int $minPlayers,
        int $maxPlayers,
        ?int $timeout
    )
    {
        $this->smallBlind = $smallBlind;
        $this->bigBlind = $bigBlind;
        $this->initialMoney = $initialMoney;
        $this->minPlayers = $minPlayers;
        $this->maxPlayers = $maxPlayers;
        $this->timeout = $timeout;
    }

    public function getSmallBlind(): int
    {
        return $this->smallBlind;
    }

    public function getBigBlind(): int
    {
        return $this->bigBlind;
    }

    public function getInitialMoney(): int
    {
        return $this->initialMoney;
    }

    public function getMaxPlayersCount(): int
    {
        return $this->maxPlayers;
    }

    public function getMinPlayersCount(): int
    {
        return $this->minPlayers;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }
}
