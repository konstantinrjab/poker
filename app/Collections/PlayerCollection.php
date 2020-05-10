<?php

namespace App\Collections;

use App\Exceptions\GameException;
use App\Models\Player;
use Illuminate\Support\Collection;

/**
 * @method Player[] getIterator()
 * @property-read Player first
 */
class PlayerCollection extends Collection
{
    private string $activePlayerId;

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

    public function getActivePlayer(): Player
    {
        if (!$this->activePlayerId) {
            $this->activePlayerId = $this->first->getId();
        }
        // TODO: make it work
        return $this->firstWhere('id', null, $this->activePlayerId);
    }

    public function setNextActivePlayer(): void
    {
        $activePlayerOffset = $this->search(function ($value, $key): bool {
            return $value->id == $this->activePlayerId;
        });
        $nextActivePlayer = $this->offsetGet($activePlayerOffset) ?: $this->first;
        $this->activePlayerId = $nextActivePlayer->getId();
    }
}
