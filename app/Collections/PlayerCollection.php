<?php

namespace App\Collections;

use App\Exceptions\GameException;
use App\Models\Player;
use Illuminate\Support\Collection;
use BadMethodCallException;

/**
 * @method Player[] getIterator()
 */
class PlayerCollection extends Collection
{
    private string $activeId;
    private string $dealerId;
    private string $smallBlindId;
    private string $bigBlindId;

    public function add($item)
    {
        /** @var Player $item */
        $duplicatesById = $this->filter(function (Player $player) use ($item) {
            return $player->getId() == $item->getId();
        });
        if (!$duplicatesById->isEmpty()) {
            throw new GameException('Player with this id has already been added');
        }
        $duplicatesByName = $this->filter(function (Player $player) use ($item) {
            return $player->getName() == $item->getName();
        });
        if (!$duplicatesByName->isEmpty()) {
            throw new GameException('Player with this name has already been added');
        }

        $this->items[] = $item;

        if (!isset($this->dealerId)) {
            $this->dealerId = $item->getId();
        } else if (!isset($this->smallBlindId)) {
            $this->smallBlindId = $item->getId();
        } else if (!isset($this->bigBlindId)) {
            $this->bigBlindId = $item->getId();
        }

        return $this;
    }

    public function getById(string $id): Player
    {
        return $this->first(function (Player $player, $key) use ($id): bool {
            return $player->getId() == $id;
        });
    }

    public function getActivePlayer(): Player
    {
        if (!isset($this->activeId)) {
            $this->activeId = $this->first()->getId();
        }
        return $this->getById($this->activeId);
    }

    public function getDealer(): Player
    {
        return $this->getById($this->dealerId);
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
        throw new GameException('Cannot resolve next active player');
    }

    public function setNextBigBlind(): void
    {
        $this->bigBlindId = $this->getNextAfterId($this->bigBlindId)->getId();
    }

    public function setNextSmallBlind(): void
    {
        $this->smallBlindId = $this->getNextAfterId($this->smallBlindId)->getId();
    }

    public function setNextDealer(): void
    {
        $this->dealerId = $this->getNextAfterId($this->dealerId)->getId();
    }

    public function getNextAfterId(string $userId): Player
    {
        $currentPlayerOffset = $this->search(function (Player $value, $key) use ($userId): bool {
            return $value->getId() == $userId;
        });
        $nextPlayer = $this->offsetExists($currentPlayerOffset + 1) ? $this->offsetGet($currentPlayerOffset + 1) : $this->first();
        return $nextPlayer;
    }
}
