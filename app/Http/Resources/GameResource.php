<?php

namespace App\Http\Resources;

use Facades\App\Http\Adapters\CardAdapter;
use App\Entities\Game;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class GameResource
 * @package App\Http\Resources
 *
 * @mixin Game
 * @property Game resource
 */
class GameResource extends JsonResource
{
    private string $userId;

    public function __construct($resource, string $userId)
    {
        $this->userId = $userId;
        parent::__construct($resource);
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
            'config' => [
                'creatorId' => $this->getCreatorId(),
                'initialMoney' => $this->getConfig()->getInitialMoney(),
                'maxPlayers' => $this->getConfig()->getMaxPlayersCount(),
                'minPlayers' => $this->getConfig()->getMinPlayersCount(),
                'smallBlind' => $this->getConfig()->getSmallBlind(),
                'bigBlind' => $this->getConfig()->getBigBlind(),
            ],
            'status' => $this->getStatus(),
            'communityCards' => $this->getDeal() && $this->getDeal()->isNeedToShowCards() ? CardAdapter::handle($this->getDeal()->showCards()) : null,
            'pot' => $this->getDeal() ? $this->getDeal()->getPot() : null,
            'players' => PlayerResource::idCollection($this->getPlayers(), $this->userId, $this->resource),
            'deal' => $this->getDeal() ? DealResource::make($this->getDeal(), $this->userId, $this->resource) : null,
        ];
    }

    private function getStatus(): string
    {
        switch ($this->resource->getStatus()):
            case Game::STATUS_WAIT_FOR_PLAYERS:
                return 'gathering';
            case Game::STATUS_STARTED:
                return 'inProgress';
            case Game::STATUS_FINISHED:
                return 'finished';
        endswitch;
    }
}
