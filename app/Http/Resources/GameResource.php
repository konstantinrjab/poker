<?php

namespace App\Http\Resources;

use Facades\App\Http\Adapters\CardAdapter;
use App\Models\Game;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class GameResource
 * @package App\Http\Resources
 *
 * @mixin Game
 */
class GameResource extends JsonResource
{
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
            'config' => [
                'creatorId' => $this->getCreatorId(),
                'initialMoney' => $this->getConfig()->getInitialMoney(),
                'maxPlayers' => $this->getConfig()->getMaxPlayersCount(),
                'minPlayers' => $this->getConfig()->getMinPlayersCount(),
                'smallBlind' => $this->getConfig()->getSmallBlind(),
                'bigBlind' => $this->getConfig()->getBigBlind(),
            ],
            'communityCards' => $this->getDeal() ? CardAdapter::handle($this->getDeal()->showCards()) : null,
            'pot' => $this->getDeal() ? $this->getDeal()->getPot() : null,
            'players' => PlayerResource::collection($this->getPlayers()),
            'deal' => $this->getDeal() ? DealResource::make($this->getDeal()) : null,
        ];
    }
}
