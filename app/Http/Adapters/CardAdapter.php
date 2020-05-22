<?php

namespace App\Http\Adapters;

use App\Models\Card;
use App\Models\Hand;

class CardAdapter
{
    public static function hand(Hand $hand): array
    {
        $cards = [];
        foreach ($hand as $card) {
            /** @var Card $card */
            $cards[] = $card->getValue() . substr($card->getSuit(), 0, 1);
        }
        return $cards;
    }
}
