<?php

namespace App\Http\Resources\Collections;

use App\Http\Resources\PlayerResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlayerResourceCollection extends AnonymousResourceCollection
{
    private string $userId;

    public function __construct($resource, $collects, string $userId)
    {
        $this->userId = $userId;
        parent::__construct($resource, $collects);
    }

    public function toArray($request)
    {
        return $this->collection->map(function (PlayerResource $resource) use ($request) {
            return $resource->setUserId($this->userId)->toArray($request);
        })->all();
    }
}
