<?php

namespace App\Http\Resources;

use App\Collections\PlayerResourceCollection;
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
            'availableActions' => $this->getActions(),
            'isFolded' => $this->getIsFolded(),
            'isCreator' => $this->isCreator(),
        ];
    }

    private function isCreator(): bool
    {
        return $this->getId() == $this->game->getCreatorId();
    }
}
