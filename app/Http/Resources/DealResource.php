<?php

namespace App\Http\Resources;

use App\Entities\Game\Deal;
use App\Entities\Game\Game;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DealResource
 * @package App\Http\Resources
 *
 * @mixin Deal
 */
class DealResource extends JsonResource
{
    private string $userId;
    private Game $game;

    public function __construct($resource, string $userId, Game $game)
    {
        $this->userId = $userId;
        $this->game = $game;
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'winners' => $this->getWinners() ? PlayerResource::idCollection($this->getWinners(), $this->userId, $this->game) : [],
        ];
    }
}
