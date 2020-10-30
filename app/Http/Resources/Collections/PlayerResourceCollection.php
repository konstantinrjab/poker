<?php

namespace App\Http\Resources\Collections;

use App\Entities\Game\Game;
use App\Http\Resources\PlayerResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlayerResourceCollection extends AnonymousResourceCollection
{
    private string $userId;
    private Game $game;

    public function __construct($resource, $collects, string $userId, Game $game)
    {
        $this->userId = $userId;
        $this->game = $game;
        parent::__construct($resource, $collects);
    }

    public function toArray($request)
    {
        return $this->collection->map(function (PlayerResource $resource) use ($request) {
            return $resource->setUserId($this->userId)->setGame($this->game)->toArray($request);
        })->all();
    }
}
