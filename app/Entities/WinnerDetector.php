<?php

namespace App\Entities;

use App\Entities\Collections\Deck;
use App\Entities\Collections\PlayerCollection;
use Illuminate\Support\Facades\Facade;

class WinnerDetector extends Facade
{
    public const ALL_FOLDED_DESCRIPTION = 'all folded';

    public function detect(Deck $deck, PlayerCollection $players): PlayerCollection
    {
        $candidates = [];
        foreach ($players as $player) {
            if (!$player->getIsFolded()) {
                $handStrength = new HandStrength($player->getHand(), $deck);
                $player->setStrength($handStrength->getStrength());
                $candidates[] = $player;
            }
        }
        if (count($candidates) == 1) {
            $candidates[0]->setStrengthDescription(self::ALL_FOLDED_DESCRIPTION);
            return new PlayerCollection($candidates);
        }

        $winners = new PlayerCollection($this->findWinners($candidates));
        foreach ($winners as $player) {
            if (!$player->getIsFolded()) {
                $handStrength = new HandStrength($player->getHand(), $deck);
                $strength = $handStrength->getStrength();
                $strengthDescription = $handStrength->getStrengthDescription();
                $player->setStrength($strength);
                $player->setStrengthDescription($strengthDescription);
                $candidates[] = $player;
            }
        }
        return $winners;
    }

    /**
     * @param  \App\Entities\Database\Game\Player[] $players
     * @param int $availableIterations
     * @return array
     */
    private function findWinners(array $players, int $availableIterations = 10): array
    {
        $max = 0;
        $points = [];
        foreach ($players as $player) {
            $strength = $player->getStrength();
            $points += $strength;
            if (max($strength) > $max) {
                $max = max($strength);
            }
        }
        foreach ($players as $playerNumber => $player) {
            if (!in_array($max, $player->getStrength())) {
                unset($players[$playerNumber]);
            }
        }

        if (count($players) == 1 || $availableIterations == 0) {
            return $players;
        }

        foreach ($players as $playerNumber => $player) {
            $strength = $player->getStrength();
            foreach ($strength as $key => $value) {
                if ($value == $max) {
                    unset($strength[$key]);
                }
            }
            $player->setStrength($strength);
        }

        return $this->findWinners($players, --$availableIterations);
    }
}
