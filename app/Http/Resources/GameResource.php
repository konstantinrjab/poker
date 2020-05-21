<?php

namespace App\Http\Resources;

use App\Models\Game;
use App\Models\GameConfig;
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
            'status' => $this->getStatus(),
            'pot' => $this->getPot(),
            'players' => PlayerResource::collection($this->getPlayers()),
            'round' => $this->getRound() ? RoundResource::make($this->getRound()) : null,
        ];
    }
}
