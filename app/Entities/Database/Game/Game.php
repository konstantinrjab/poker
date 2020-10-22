<?php

namespace App\Entities\Database\Game;

use App\Entities\Collections\PlayerCollection;
use App\Entities\Database\RedisORM;
use App\Exceptions\GameException;
use App\Dispatchable\Jobs\NotifyGameUpdated;
use Illuminate\Support\Str;

class Game extends RedisORM
{
    public const STATUS_WAIT_FOR_PLAYERS = 'waiting';
    public const STATUS_STARTED = 'started';
    public const STATUS_FINISHED = 'finished';

    private string $id;
    private string $creatorId;
    private string $status;
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

    public function getStatus(): string
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
        if ($this->status != self::STATUS_WAIT_FOR_PLAYERS) {
            throw new GameException('Cannot start game with status: ' . $this->status);
        }
        if ($this->players->count() < $this->config->getMinPlayersCount()) {
            throw new GameException('There is not enough players to start the game');
        }
        foreach ($this->players as $player) {
            if (!$player->getIsReady()) {
                throw new GameException('Player ' . $player->getId() . ' is not ready yet');
            }
        }
        $this->deal = new Deal($this->players, $this->config);
        $this->status = self::STATUS_STARTED;
        $this->deal->getRound()->initBlinds();
    }

    public function getDeal(): ?Deal
    {
        return isset($this->deal) ? $this->deal : null;
    }

    public function onAfterUpdate(): void
    {
        $deal = $this->getDeal();

        $lastTurnReached = $deal->getRound()->shouldEnd() && $deal->getStatus() == Deal::STATUS_RIVER;

        if ($lastTurnReached || $deal->getRound()->isOnlyOnePlayerNotFolded()) {
            $deal->end();
        } else if ($deal->getRound()->shouldEnd() && $deal->getStatus() != Deal::STATUS_RIVER) {
            $deal->startNextRound();
        } else {
            $this->players->setNextActivePlayer();
        }
    }

    public function createNewDeal()
    {
        $this->players->prepareForNextDeal($this->getConfig());
        $this->deal = new Deal($this->players, $this->getConfig());
    }

    protected static function getKey(): string
    {
        return 'game';
    }

    public function save(bool $notifyPlayers = true)
    {
        parent::save();
        if ($notifyPlayers) {
            NotifyGameUpdated::dispatchAfterResponse($this);
        }
    }
}
