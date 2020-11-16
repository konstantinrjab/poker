<?php

namespace App\Entities\Game;

use App\Entities\Collections\PlayerCollection;
use App\Entities\Database\RedisORM;
use App\Exceptions\GameException;
use App\Dispatchable\Jobs\NotifyGameUpdated;
use App\Dispatchable\Jobs\CheckAndFoldInactivePlayer;

class Game extends RedisORM
{
    public const STATUS_WAIT_FOR_PLAYERS = 'waiting';
    public const STATUS_STARTED = 'started';
    public const STATUS_FINISHED = 'finished';

    private string $creatorId;
    private string $status;
    private Deal $deal;
    private PlayerCollection $players;
    private GameConfig $config;

    public function __construct(GameConfig $config, string $creatorId)
    {
        $this->creatorId = $creatorId;
        $this->players = new PlayerCollection();
        $this->status = self::STATUS_WAIT_FOR_PLAYERS;
        $this->config = $config;
        parent::__construct();
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
        $this->initInactivePlayerJob();
    }

    public function getDeal(): ?Deal
    {
        return isset($this->deal) ? $this->deal : null;
    }

    public function onBeforeUpdate(): void
    {
        if ($this->status == self::STATUS_FINISHED) {
            throw new GameException('Game finished');
        }
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

        $this->save();
        $this->initInactivePlayerJob();
    }

    public function checkForNewDeal(): void
    {
        if ($this->deal->getStatus() == Deal::STATUS_END) {
            /** @var Game $clonedGame */
            $clonedGame = unserialize(serialize($this)); // deep clone for nested objects - deal, players etc
            $clonedGame->createNewDealOrEnd();
            $clonedGame->save();
        }
    }

    public function createNewDealOrEnd(): void
    {
        $this->players->kickWithoutEnoughMoney($this->getConfig()->getBigBlind());
        if ($this->players->count() == 1) {
            $this->status = self::STATUS_FINISHED;
            return;
        }
        $this->players->prepareForNextDeal();
        $this->deal = new Deal($this->players, $this->getConfig());
        $this->initInactivePlayerJob();
    }

    protected static function getKey(): string
    {
        return 'game';
    }

    protected function afterSave(): void
    {
        if (env('SOCKETS_ENABLED', true)) {
            NotifyGameUpdated::dispatch($this)
                ->delay(0);
        }
    }

    private function initInactivePlayerJob(): void
    {
        CheckAndFoldInactivePlayer::dispatch($this)
            ->delay($this->config->getTimeout());
    }
}
