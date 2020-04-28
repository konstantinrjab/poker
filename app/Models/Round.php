<?php

namespace app\Models;

use app\Collections\PlayersCollection;

class Round
{
    private Deck $deck;
    private PlayersCollection $playerCollection;

    public function __construct()
    {
        $this->deck = Deck::getFull();
    }

    public function addPlayers(PlayersCollection $playerCollection): void
    {
        $this->playerCollection = $playerCollection;
    }

    public function getWinner(): Player
    {
        foreach ($this->playerCollection as $player) {
            $playerStrengthDeck = new HandStrength($player->getDeck()->merge($this->deck)->values());
            $player->setStrength($playerStrengthDeck->getStrength());
        }
        return $this->playerCollection->max('strength');
    }
}
