<?php

namespace App\Collections;

use App\Exceptions\GameException;
use App\Models\Player;
use Illuminate\Support\Collection;

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
        $duplicates = $this->filter(function ($player) use ($item) {
            return $player->getId() == $item->getId();
        });
        if (!$duplicates->isEmpty()) {
            throw new GameException('This player has already been added');
        }

        $this->items[] = $item;

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

    public function setNextActivePlayer(): void
    {
        $this->activeId = $this->getNextAfterId($this->activeId)->getId();
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

    private function getNextAfterId(string $userId): Player
    {
        $currentPlayerOffset = $this->search(function (Player $value, $key) use ($userId): bool {
            return $value->getId() == $userId;
        });
        // TODO: check this
        $nextPlayer = $this->offsetGet($currentPlayerOffset) ?? $this->first()->getId();
        return $nextPlayer;
    }
}
