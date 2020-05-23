<?php

namespace App\Http\Resources;

use App\Models\Deal;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class DealResource
 * @package App\Http\Resources
 *
 * @mixin Deal
 */
class DealResource extends JsonResource
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
            'status' => $this->getStatus(),
            'winners' => $this->getWinners(),
        ];
    }
}
