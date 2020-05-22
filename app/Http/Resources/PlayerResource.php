<?php

namespace App\Http\Resources;

use App\Collections\PlayerResourceCollection;
use App\Http\Adapters\CardAdapter;
use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PlayerResource
 * @package App\Http\Resources
 *
 * @mixin Player
 */
class PlayerResource extends JsonResource
{
    private Game $game;

    public function setGame(Game $game)
    {
        $this->game = $game;
        return $this;
    }

    public static function collection($playerCollection): PlayerResourceCollection
    {
        $players = [];
        foreach ($playerCollection as $player) {
            $players[] = PlayerResource::make($player);
        }
        return new PlayerResourceCollection($players);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'money' => $this->getMoney(),
            'bet' => $this->getBet(),
            'isReadyToStart' => $this->getIsReady(),
            'isFolded' => $this->getIsFolded(),
            'isCreator' => $this->isCreator(),
            'isDealer' => $this->isDealer(),
            'isBigBlind' => $this->isBigBlind(),
            'isSmallBlind' => $this->isSmallBlind(),
            'isActive' => $this->isActive(),
            // TODO: finish this
            'holeCards' => $this->getHand() ? CardAdapter::hand($this->getHand()) : [],
            'availableActions' => $this->getActions(),
        ];
    }

    private function isCreator(): bool
    {
        return $this->getId() == $this->game->getCreatorId();
    }

    private function isDealer(): bool
    {
        return $this->game->getRound() ? $this->getId() == $this->game->getRound()->getPlayerCollection()->getDealer()->getId() : false;
    }

    private function isSmallBlind(): bool
    {
        return $this->game->getRound() ? $this->getId() == $this->game->getRound()->getPlayerCollection()->getSmallBlind()->getId() : false;
    }

    private function isBigBlind(): bool
    {
        return $this->game->getRound() ? $this->getId() == $this->game->getRound()->getPlayerCollection()->getBigBlind()->getId() : false;
    }

    private function isActive(): bool
    {
        return $this->game->getRound() ? $this->getId() == $this->game->getRound()->getPlayerCollection()->getActivePlayer()->getId() : false;
    }
}
