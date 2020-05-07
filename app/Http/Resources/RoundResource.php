<?php

namespace App\Http\Resources;

use App\Models\Round;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RoundResource
 * @package App\Http\Resources
 *
 * @mixin Round
 */
class RoundResource extends JsonResource
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
            'activePlayer' => $this->getActivePlayer(),
        ];
    }
}
