<?php

namespace App\Collections;

use App\Http\Resources\PlayerResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlayerResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->map(function (PlayerResource $resource) use ($request) {
            return $resource->additional($this->additional)->toArray($request);
        })->all();
    }
}
