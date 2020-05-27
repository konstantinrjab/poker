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
            'bet' => $this->game->getDeal() ? $this->game->getDeal()->getRound()->getPlayerBet($this->getId()) : null,
            'isReadyToStart' => $this->getIsReady(),
            'isFolded' => $this->getIsFolded(),
            'isCreator' => $this->isCreator(),
            'isDealer' => $this->isDealer(),
            'isBigBlind' => $this->isBigBlind(),
            'isSmallBlind' => $this->isSmallBlind(),
            'isActive' => $this->isActive(),
            // TODO: finish this
            'holeCards' => $this->getHand() ? CardAdapter::handle($this->getHand()) : [],
            'availableActions' => $this->getActions(),
        ];
    }

    private function isCreator(): bool
    {
        return $this->getId() == $this->game->getCreatorId();
    }

    private function isDealer(): bool
    {
        return $this->getId() == $this->game->getPlayers()->getDealer()->getId();
    }

    private function isSmallBlind(): bool
    {
        return $this->getId() == $this->game->getPlayers()->getSmallBlind()->getId();
    }

    private function isBigBlind(): bool
    {
        return $this->getId() == $this->game->getPlayers()->getBigBlind()->getId();
    }

    private function isActive(): bool
    {
        return $this->getId() == $this->game->getPlayers()->getActivePlayer()->getId();
    }
}
