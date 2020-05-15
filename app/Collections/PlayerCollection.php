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

    public function getActivePlayer(): Player
    {
        if (!isset($this->activeId)) {
            $this->activeId = $this->first()->getId();
        }
        return $this->first(function (Player $player, $key): bool {
            return $player->getId() == $this->activeId;
        });
    }

    public function setNextActivePlayer(): void
    {
        $activePlayerOffset = $this->search(function ($value, $key): bool {
            return $value->id == $this->activeId;
        });
        $nextActivePlayer = $this->offsetGet($activePlayerOffset) ?: $this->first;
        $this->activeId = $nextActivePlayer->getId();
    }
}
