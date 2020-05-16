<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Exceptions\GameException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class Game
{
    private const MAX_PLAYERS = 8;
    private const MINIMUM_PLAYERS_COUNT = 5;
    private const STATUS_WAIT_FOR_PLAYERS = 1;
    private const STATUS_STARTED = 2;
    private const STATUS_END = 3;

    private string $id;
    private string $creatorId;
    private int $status;
    private ?Round $round = null;
    private PlayerCollection $playerCollection;
    private int $pot;

    public static function get(string $id, bool $throwOnNotFound = true): ?Game
    {
        $game = Redis::get('game:' . $id);
        if (!$game && $throwOnNotFound) {
            throw new ModelNotFoundException();
        }
        return $game ? unserialize($game) : null;
    }

    public function __construct(string $creatorId)
    {
        $this->creatorId = $creatorId;
        $this->playerCollection = new PlayerCollection();
        $this->id = Str::uuid();
        $this->status = self::STATUS_WAIT_FOR_PLAYERS;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatorId(): string
    {
        return $this->creatorId;
    }

    public function addPlayer(Player $player): void
    {
        if ($this->playerCollection->count() >= self::MAX_PLAYERS) {
            throw new GameException('Cannot add more players, game is full');
        }
        $this->playerCollection->add($player);
    }

    public function getPlayers(): PlayerCollection
    {
        return $this->playerCollection;
    }

    public function start(): void
    {
        if ($this->status == self::STATUS_STARTED) {
            throw new GameException('Game already started');
        }
        if ($this->status == self::STATUS_END) {
            throw new GameException('Game was ended');
        }
        if ($this->playerCollection->count() < self::MINIMUM_PLAYERS_COUNT) {
            throw new GameException('There is not enough players to start the game');
        }
        // TODO: set big blind dynamically
        $this->round = new Round($this->playerCollection, 10);
        $this->status = self::STATUS_STARTED;
        $this->save();
    }

    public function end(): void
    {
        $this->status = self::STATUS_END;
    }

    public function getRound(): ?Round
    {
        return $this->round;
    }

    public function save()
    {
        Redis::set('game:' . $this->getId(), serialize($this));
    }
}
