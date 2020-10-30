<?php

namespace App\Entities\Collections;

use App\Entities\Game\GameConfig;
use App\Entities\Game\Player;
use Illuminate\Support\Collection;
use Exception;

/**
 * @method Player[] getIterator()
 * @method Player offsetGet(int $key)
 */
class PlayerCollection extends Collection
{
    private string $activeId;
    private string $dealerId;
    private string $smallBlindId;
    private string $bigBlindId;

    /**
     * @param Player $player
     * @return static
     * @throws Exception
     */
    public function add($player)
    {
        if (!$player instanceof Player) {
            throw new Exception('$player should be instance of Player');
        }

        $duplicatesByName = $this->filter(function (Player $existedPlayer) use ($player) {
            return $existedPlayer->getName() == $player->getName();
        });
        if (!$duplicatesByName->isEmpty()) {
            $name = $player->getName() . ($duplicatesByName->count() + 1);
            $player->setName($name);
        }

        $this->items[] = $player;

        if ($this->count() == 1) {
            $this->dealerId = $player->getId();
            $this->smallBlindId = $player->getId();
        } elseif ($this->count() == 2) {
            $this->bigBlindId = $player->getId();
        } elseif ($this->count() == 3) {
            $this->dealerId = $this->offsetGet(0)->getId();
            $this->smallBlindId = $this->offsetGet(1)->getId();
            $this->bigBlindId = $this->offsetGet(2)->getId();
        }

        return $this;
    }

    public function getById(string $id): Player
    {
        return $this->first(function (Player $player, $key) use ($id): bool {
            return $player->getId() == $id;
        });
    }

    public function getActivePlayer(): ?Player
    {
        return isset($this->activeId) ? $this->getById($this->activeId): null;
    }

    public function getDealer(): ?Player
    {
        return isset($this->dealerId) ? $this->getById($this->dealerId) : null;
    }

    public function getBigBlind(): ?Player
    {
        return isset($this->bigBlindId) ? $this->getById($this->bigBlindId) : null;
    }

    public function getSmallBlind(): ?Player
    {
        return isset($this->smallBlindId) ? $this->getById($this->smallBlindId) : null;
    }

    public function setActivePlayer(string $playerId): void
    {
        $this->activeId = $playerId;
    }

    public function setNextActivePlayer(): void
    {
        $nextPlayer = $this->getNextAfterId($this->activeId);
        foreach (range(0, $this->count()) as $playerNumber) {
            if ($nextPlayer->getIsFolded()) {
                $nextPlayer = $this->getNextAfterId($nextPlayer->getId());
                continue;
            }
            $this->activeId = $nextPlayer->getId();
            return;
        }
        throw new Exception('Cannot resolve next active player');
    }

    public function prepareForNextDeal(GameConfig $config): void
    {
        $this->items = $this->reject(function (Player $player, $key) use ($config): bool {
            return $player->getMoney() < $config->getBigBlind();
        });

        foreach ($this as $player) {
            $player->setIsFolded(false);
        }

        $this->dealerId = $this->getNextAfterId($this->dealerId)->getId();
        $this->smallBlindId = $this->getNextAfterId($this->smallBlindId)->getId();
        $this->bigBlindId = $this->getNextAfterId($this->bigBlindId)->getId();
    }

    public function getNextAfterId(string $userId): Player
    {
        $currentPlayerOffset = $this->search(function (Player $value, $key) use ($userId): bool {
            return $value->getId() == $userId;
        });
        return $this->offsetExists($currentPlayerOffset + 1) ? $this->offsetGet($currentPlayerOffset + 1) : $this->first();
    }
}
