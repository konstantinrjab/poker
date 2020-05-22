<?php

namespace App\Collections;


use App\Http\Resources\PlayerResource;
use App\Models\Game;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlayerResourceCollection extends ResourceCollection
{
    private Game $game;

    public function setGame(game $game)
    {
        $this->game = $game;
    }

    public function toArray($request)
    {
        return $this->collection->map(function (PlayerResource $resource) use ($request) {
            return $resource->setGame($this->game)->toArray($request);
        })->all();
    }
}
