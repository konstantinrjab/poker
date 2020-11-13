<?php

namespace Tests\Feature;

use App\Entities\Actions\ActionFactory;
use App\Entities\Game\Game;
use App\Entities\Game\GameConfig;
use App\Entities\Game\Player;
use App\Entities\User;
use App\Http\Requests\UpdateGameRequest;
use Tests\TestCase;

class KickPlayerWithoutEnoughMoneyTest extends TestCase
{
    public function testKick()
    {
        $user1 = new User('username1');
        $user1->save();

        $user2 = new User('username2');
        $user2->save();

        $config = new GameConfig(10, 20, 20, 2, 5, null);
        $game = new Game($config, $user1->getId());

        $player1 = new Player($user1->getId(), $user1->getName(), $game->getConfig()->getInitialMoney());
        $game->getPlayers()->add($player1);

        $player2 = new Player($user2->getId(), $user2->getName(), $game->getConfig()->getInitialMoney());
        $game->getPlayers()->add($player2);

        $player1->setIsReady(true);
        $player2->setIsReady(true);
        $this->app->singleton('game', function () use ($game) {
            return $game;
        });
        $game->start();

        $request = new UpdateGameRequest();
        $request->server->set('REQUEST_METHOD', 'POST');
        $request->request->set('userId', $user1->getId());
        $request->request->set('action', 'call');
        (ActionFactory::get($request, $game))->updateGame($game, $request);
        $game->onAfterUpdate();
        $game->save();

        $request = new UpdateGameRequest();
        $request->server->set('REQUEST_METHOD', 'POST');
        $request->request->set('userId', $user2->getId());
        $request->request->set('action', 'fold');
        (ActionFactory::get($request, $game))->updateGame($game, $request);
        $game->onAfterUpdate();
        $this->assertTrue($game->getPlayers()->getById($user2->getId())->getMoney() == 0);

        $game->createNewDealOrEnd();
        $game->save();

        $this->assertIsObject($game->getPlayers()->getById($user1->getId()));
        $this->assertTrue($game->getPlayers()->count() == 1); // player2 is kicked
    }
}
