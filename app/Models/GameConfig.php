<?php

namespace App\Models;

class GameConfig
{
    private const MIN_PLAYERS_COUNT = 3;

    private int $bigBlind;
    private int $smallBlind;
    private int $initialMoney;
    private int $maxPlayers;

    public function __construct(
        int $smallBlind,
        int $bigBlind,
        int $initialMoney,
        int $maxPlayers
    )
    {
        $this->smallBlind = $smallBlind;
        $this->bigBlind = $bigBlind;
        $this->initialMoney = $initialMoney;
        $this->maxPlayers = $maxPlayers;
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
        return self::MIN_PLAYERS_COUNT;
    }
}
