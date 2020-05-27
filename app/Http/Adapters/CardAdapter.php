<?php

namespace App\Http\Adapters;

use App\Models\Card;
use Illuminate\Support\Collection;
use Exception;

class CardAdapter
{
    public function handle(Collection $cardCollection): array
    {
        $cards = [];
        foreach ($cardCollection as $card) {
            /** @var Card $card */
            $cards[] = self::getValue($card) . substr($card->getSuit(), 0, 1);
        }
        return $cards;
    }

    private function getValue(Card $card): string
    {
        if ($card->getValue() <= 10) {
            return $card->getValue();
        }
        if ($card->getValue() == 11) {
            return 'J';
        }
        if ($card->getValue() == 12) {
            return 'Q';
        }
        if ($card->getValue() == 13) {
            return 'K';
        }
        if ($card->getValue() == 14) {
            return 'A';
        }
        throw new Exception('Invalid card value: ' . $card->getValue());
    }
}
