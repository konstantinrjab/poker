<?php

namespace App\Models;

use App\Collections\PlayerCollection;
use App\Collections\Deck;

class Round
{
    private Deck $deck;
    private PlayerCollection $playerCollection;

    public function __construct(PlayerCollection $playerCollection)
    {
        $deck = Deck::getFull();
        $this->playerCollection = $playerCollection;
        foreach ($this->playerCollection as $player) {
            $player->setHand($deck->getHand());
        }
        // TODO: move it to const
        $this->deck = $deck->take(5);
    }

    public function getWinners(): PlayerCollection
    {
        foreach ($this->playerCollection as $player) {
            $playerStrengthDeck = new HandStrength($player->getHand(), $this->deck);
            $player->setStrength($playerStrengthDeck->getStrength());
        }
        $maxStrength = $this->playerCollection->max(function (Player $player): int {
            return $player->getStrength();
        });
        $winners = $this->playerCollection->filter(function (Player $player) use ($maxStrength): bool {
            return $player->getStrength() == $maxStrength;
        });
        return $winners;
    }
}
