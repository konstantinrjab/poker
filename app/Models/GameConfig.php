<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class GameConfig
{
    private const MIN_PLAYERS_COUNT = 4;
    private const MAX_PLAYERS_COUNT = 8;

    private int $bigBlind;
    private int $smallBlind;
    private int $initialMoney;

    public function __construct(int $smallBlind, int $bigBlind, int $initialMoney)
    {
        $this->smallBlind = $smallBlind;
        $this->bigBlind = $bigBlind;
        $this->initialMoney = $initialMoney;
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
        return self::MAX_PLAYERS_COUNT;
    }

    public function getMinPlayersCount(): int
    {
        return self::MIN_PLAYERS_COUNT;
    }
}
