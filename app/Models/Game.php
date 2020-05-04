<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Exceptions\GameException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class Game
{
    private string $creatorId;
    private string $id;
    private Round $round;
    private State $state;
    private PlayerCollection $playerCollection;

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
        if ($this->state->getStatus() == State::STATUS_STARTED) {
            throw new GameException('Game already started');
        }
        if ($this->state->getStatus() == State::STATUS_END) {
            throw new GameException('Game was ended');
        }
        if (!$this->isReadyToStart()) {
            throw new GameException('Game is not ready to start');
        }
        $this->round = new Round($this->playerCollection);
        $this->state->setStatus(State::STATUS_STARTED);
        $this->save();
    }

    public function end(): void
    {
        $this->state->setStatus(State::STATUS_END);
    }

    public function getRound(): Round
    {
        return $this->round;
    }

    public function save()
    {
        Redis::set('game:' . $this->getId(), serialize($this));
    }
}
