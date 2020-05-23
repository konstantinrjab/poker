<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class Game
{
    private const STATUS_WAIT_FOR_PLAYERS = 1;
    private const STATUS_STARTED = 2;
    private const STATUS_END = 3;

    private string $id;
    private string $creatorId;
    private int $status;
    private ?Round $round = null;
    private PlayerCollection $players;
    private GameConfig $config;
    private int $pot;

    public static function get(string $id, bool $throwOnNotFound = true): ?Game
    {
        $game = Redis::get('game:' . $id);
        if (!$game && $throwOnNotFound) {
            throw new ModelNotFoundException();
        }
        return $game ? unserialize($game) : null;
    }

    public function __construct(CreateGameRequest $request)
    {
        $this->creatorId = $request->input('userId');
        $this->players = new PlayerCollection();
        $this->id = Str::uuid();
        $this->status = self::STATUS_WAIT_FOR_PLAYERS;
        // TODO: make it dynamic
        $this->pot = 1000;
        $this->config = new GameConfig(
            $request->input('smallBlind'),
            $request->input('bigBlind'),
            $request->input('initialMoney')
        );
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

    public function getPot(): int
    {
        return $this->pot;
    }

    public function getConfig(): GameConfig
    {
        return $this->config;
    }

    public function getPlayers(): PlayerCollection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): void
    {
        if ($this->players->count() >= $this->config->getMaxPlayersCount()) {
            throw new GameException('Cannot add more players, game is full');
        }
        $this->players->add($player);
    }

    public function start(): void
    {
        if ($this->status == self::STATUS_STARTED) {
            throw new GameException('Game already started');
        }
        if ($this->status == self::STATUS_END) {
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
        $this->round = new Round($this->players, $this->config, true);
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
