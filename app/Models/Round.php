<?php

namespace App\Models;

use App\Collections\PlayerCollection;

class Round
{
    private Deck $deck;
    private PlayerCollection $playerCollection;

    public function __construct()
    {
        $this->deck = Deck::getFull();
    }

    public function addPlayers(PlayerCollection $playerCollection): void
    {
        $this->playerCollection = $playerCollection;
    }

    public function getWinner(): Player
    {
        foreach ($this->playerCollection as $player) {
            $playerStrengthDeck = new HandStrength($player->getHand(), $this->deck->values());
            $player->setStrength($playerStrengthDeck->getStrength());
        }
        return $this->playerCollection->max('strength');
    }

    public function start(): void
    {
        foreach ($this->playerCollection as $player) {
            for ($cardCount = 1; $cardCount <= Hand::CARD_LIMIT; $cardCount++) {
                $player->getHand()->add($this->deck->random());
            }
        }
    }
}
