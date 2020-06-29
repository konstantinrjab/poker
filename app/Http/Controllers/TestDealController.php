<?php

namespace App\Http\Controllers;

use App\Collections\Deck;
use App\Exceptions\GameException;
use App\Http\Requests\CreateTestDealRequest;
use App\Models\Card;
use App\Models\Deal;
use App\Models\Game;
use App\Models\GameConfig;
use App\Models\Player;

class TestDealController extends Controller
{
    /**
     * @param CreateTestDealRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\GameException
     */
    public function __invoke(CreateTestDealRequest $request)
    {
        $config = new GameConfig(10, 20, 500);
        $game = new Game($config, $request->input('users')[0]['id']);
        foreach ($request->input('users') as $user) {
            $player = new Player(
                $user['id'],
                $user['name'],
                $game->getConfig()->getInitialMoney()
            );
            $player->setIsReady(true);

            // TODO: refactor this if needed
            if ($game->players->count() >= $game->config->getMaxPlayersCount()) {
                throw new GameException('Cannot add more players, game is full');
            }
            $game->players->add($player);
        }

        $game->start();

        $deck = new Deck();
        foreach ($request->input('tableCards') as $card) {
            $deck->add(new Card($card['suit'], $card['value']));
        }
        $reflectionProperty = new \ReflectionProperty(Deal::class, 'deck');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($game->getDeal(), $deck);

        $reflector = new \ReflectionObject($game->getDeal());
        $method = $reflector->getMethod('calculateWinners');
        $method->setAccessible(true);
        $method->invoke($game->getDeal());

        $winners = $game->getDeal()->getWinners();

        $result= [];
        foreach ($winners as $winner) {
            $result[] = $winner->getId();
        }
        return response()->json([
            'winners' => $result
        ]);
    }
}
