<?php

namespace App\Http\Controllers;

use App\Entities\Game\Deal;
use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Requests\ReadyRequest;
use App\Http\Requests\ShowGameRequest;
use App\Http\Requests\StartGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Entities\Actions\ActionFactory;
use App\Entities\Game\Game;
use App\Entities\Game\GameConfig;
use App\Entities\Game\Player;
use App\Entities\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App;

class GameController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateGameRequest $request
     * @return GameResource
     * @throws GameException
     */
    public function store(CreateGameRequest $request)
    {
        $config = new GameConfig(
            $request->input('smallBlind'),
            $request->input('bigBlind'),
            $request->input('initialMoney'),
            $request->input('minPlayers'),
            $request->input('maxPlayers')
        );

        $user = User::get($request->input('userId'), false);
        if (!$user) {
            throw new UnauthorizedHttpException('', 'User with this id not found');
        }

        $player = new Player($user->getId(), $user->getName(), $config->getInitialMoney());
        $game = new Game($config, $player->getId());
        $game->getPlayers()->add($player);
        $game->save(false);

        return GameResource::make($game, $user->getId());
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @param ShowGameRequest $request
     * @return GameResource
     */
    public function show(string $id, ShowGameRequest $request)
    {
        $game = Game::get($id);
        $userId = $request->input('userId');
        $exists = $game->getPlayers()->first(function (Player $player) use ($userId) {
            return $player->getId() == $userId;
        });
        if (!$exists) {
            throw new NotFoundHttpException();
        }

        return GameResource::make($game, $userId);
    }

    /**
     * @param JoinGameRequest $request
     * @param string $id
     * @throws GameException
     * @return GameResource
     */
    public function join(string $id, JoinGameRequest $request)
    {
        $game = Game::get($id);
        $userId = $request->input('userId');

        $alreadyJoined = $game->getPlayers()->first(function (Player $existedPlayer) use ($userId) {
            return $existedPlayer->getId() == $userId;
        });

        if ($alreadyJoined) {
            return GameResource::make($game, $userId);
        }

        if ($game->getPlayers()->count() >= $game->getConfig()->getMaxPlayersCount()) {
            throw new GameException('Game is full');
        }

        $user = User::get($userId, false);
        if (!$user) {
            throw new UnauthorizedHttpException('', 'User with this id not found');
        }

        $player = new Player($user->getId(), $user->getName(), $game->getConfig()->getInitialMoney());
        $game->getPlayers()->add($player);
        $game->save();

        return GameResource::make($game, $userId);
    }

    public function ready(string $id, ReadyRequest $request)
    {
        $game = Game::get($id);
        $userId = $request->input('userId');
        $player = $game->getPlayers()->getById($userId);

        $player->setIsReady($request->input('value'));
        $game->save();

        return GameResource::make($game, $userId);
    }

    /**
     * @param StartGameRequest $request
     * @param string $id
     * @throws GameException
     * @return GameResource
     */
    public function start(string $id, StartGameRequest $request)
    {
        $game = Game::get($id);
        $userId = $request->input('userId');

        $canStartGamePlayerId = $game->getPlayers()->getDealer() ? $game->getPlayers()->getDealer()->getId() : $game->getCreatorId();
        if ($canStartGamePlayerId != $userId) {
            throw new GameException('You cannot start game');
        }
        $game->start();
        $game->save();

        return GameResource::make($game, $userId);
    }

    /**
     * @param UpdateGameRequest $request
     * @param string $id
     * @return GameResource
     * @throws GameException
     */
    public function update(string $id, UpdateGameRequest $request)
    {
        $game = Game::get($id);
        // TODO: add timeout logic
        $userId = $request->input('userId');
        if ($game->getPlayers()->getActivePlayer()->getId() != $userId) {
            throw new GameException('It is not you turn');
        }
        if ($game->getDeal()->getStatus() == Deal::STATUS_END) {
            throw new GameException('Deal ended');
        }
        $action = ActionFactory::get($request, $game);
        $action->updateGame($game, $request);

        $game->onAfterUpdate();
        $game->save();

        $response = GameResource::make($game, $userId);

        if ($game->getDeal()->getStatus() == Deal::STATUS_END) {
            /** @var Game $clonedGame */
            $clonedGame = unserialize(serialize($game)); // deep clone for nested objects - deal, players etc
            $clonedGame->createNewDeal();
            $clonedGame->save();
        }

        return $response;
    }
}
