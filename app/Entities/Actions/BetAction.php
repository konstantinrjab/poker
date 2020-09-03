<?php

namespace App\Entities\Actions;

use App\Exceptions\GameException;
use App\Http\Requests\UpdateGameRequest;
use App\Entities\Game;

class BetAction extends Action
{
    public static function getName(): string
    {
        return 'bet';
    }

    public static function getMinBet(Game $game): int
    {
        $bigBlindAmount = $game->getConfig()->getBigBlind();
        $minRaise = $bigBlindAmount * 2;
        $maxBet = $game->getDeal()->getRound()->getMaxBet();
        if ($minRaise <= $maxBet) {
            $minRaise = $maxBet + $bigBlindAmount;
        }

        return $minRaise;
    }

    public function updateGame(Game $game, UpdateGameRequest $request): void
    {
        $value = $request->input('value');
        $userId = $request->input('userId');

        $minBet = self::getMinBet($game);
        if ($value < $minBet) {
            throw new GameException('Bet has to be greater than ' . $minBet);
        }
        $player = $game->getPlayers()->getById($userId);
        if ($player->getMoney() < $value) {
            throw new GameException('You dont have enough money');
        }
        $game->getDeal()->getRound()->bet($userId, $value);
    }
}
