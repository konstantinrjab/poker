<?php

namespace App\Http\Resources;

use Facades\App\Http\Adapters\CardAdapter;
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
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $game = $this->getGame();
        $userId = $this->getUserId();

        return [
            'id' => $this->when($this->getId() == app('game.userId'), $this->getId()),
            'name' => $this->getName(),
            'money' => $this->getMoney(),
            'bet' => $game->getDeal() ? $game->getDeal()->getRound()->getPlayerBet($this->getId()) : null,
            'isReadyToStart' => $this->getIsReady(),
            'isFolded' => $this->getIsFolded(),
            'isCreator' => $this->isCreator(),
            'isDealer' => $this->isDealer(),
            'isBigBlind' => $this->isBigBlind(),
            'isSmallBlind' => $this->isSmallBlind(),
            'isActive' => $this->isActive(),
            'holeCards' => $this->getId() == $userId && $this->getHand() ? CardAdapter::handle($this->getHand()) : [],
            'availableActions' => $game->getDeal() && $game->getDeal()->getRound() ? $game->getDeal()->getRound()->getAvailableActions($this->resource) : null,
        ];
    }

    private function isCreator(): bool
    {
        return $this->getId() == $this->getGame()->getCreatorId();
    }

    private function isDealer(): bool
    {
        return $this->getId() == $this->getGame()->getPlayers()->getDealer()->getId();
    }

    private function isSmallBlind(): bool
    {
        $smallBlind = $this->getGame()->getPlayers()->getSmallBlind();
        return $smallBlind && $this->getId() == $smallBlind->getId();
    }

    private function isBigBlind(): bool
    {
        $bigBlind = $this->getGame()->getPlayers()->getBigBlind();
        return $bigBlind && $this->getId() == $bigBlind->getId();
    }

    private function isActive(): bool
    {
        return $this->getId() == $this->getGame()->getPlayers()->getActivePlayer()->getId();
    }

    private function getGame(): Game
    {
        return app()->get('game.instance');
    }

    private function getUserId(): string
    {
        return app()->get('game.userId');
    }
}
