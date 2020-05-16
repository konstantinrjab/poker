<?php

namespace App\Http\Resources;

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
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'isReady' => $this->getIsReady(),
            'isFolded' => $this->getIsFolded(),
            'bet' => $this->getBet(),
            'money' => $this->getMoney(),
        ];
    }
}
