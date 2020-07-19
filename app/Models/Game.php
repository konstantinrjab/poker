<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Database\RedisORM;
use App\Exceptions\GameException;
use Illuminate\Support\Str;

class Game extends RedisORM
{
    public const STATUS_WAIT_FOR_PLAYERS = 1;
    public const STATUS_STARTED = 2;
    public const STATUS_FINISHED = 3;

    private string $id;
    private string $creatorId;
    private int $status;
    private Deal $deal;
    private PlayerCollection $players;
    private GameConfig $config;

    public function __construct(GameConfig $config, string $creatorId)
    {
        $this->creatorId = $creatorId;
        $this->players = new PlayerCollection();
        $this->id = Str::uuid();
        $this->status = self::STATUS_WAIT_FOR_PLAYERS;
        $this->config = $config;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatorId(): string
    {
        return $this->creatorId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getConfig(): GameConfig
    {
        return $this->config;
    }

    public function getPlayers(): PlayerCollection
    {
        return $this->players;
    }

    public function start(): void
    {
        if ($this->status == self::STATUS_STARTED) {
            throw new GameException('Game already started');
        }
        if ($this->status == self::STATUS_FINISHED) {
            throw new GameException('Game was ended');
        }
        if ($this->players->count() < $this->config->getMinPlayersCount()) {
            throw new GameException('There is not enough players to start the game');
        }
        foreach ($this->players as $player) {
            if (!$player->getIsReady()) {
                throw new GameException('Player ' . $player->getId() . ' is not ready yet');
            }
        }
        $this->deal = new Deal($this->players, $this->config, true);
        $this->status = self::STATUS_STARTED;
        $this->save();
    }

    public function end(): void
    {
        $this->status = self::STATUS_FINISHED;
    }

    public function getDeal(): ?Deal
    {
        return isset($this->deal) ? $this->deal : null;
    }

    protected static function getKey(): string
    {
        return 'game';
    }
}
