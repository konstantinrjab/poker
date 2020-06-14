<?php

namespace App\Http\Controllers;

use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Requests\ReadyRequest;
use App\Http\Requests\ShowGameRequest;
use App\Http\Requests\StartGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Actions\Factories\ActionFactory;
use App\Models\Game;
use App\Models\GameConfig;
use App\Models\Player;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateGameRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws GameException
     */
    public function store(CreateGameRequest $request)
    {
        $config = new GameConfig(
            $request->input('smallBlind'),
            $request->input('bigBlind'),
            $request->input('initialMoney')
        );
        $game = new Game($config, $request->input('userId'));
        $game->addPlayer(new Player(
            $request->input('userId'),
            $request->input('name'),
            $game->getConfig()->getInitialMoney()
        ));
        $game->save();

        return response()->json([
            'gameId' => $game->getId()
        ]);
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
        $playerId = $request->input('userId');
        $exists = $game->getPlayers()->first(function (Player $player) use ($playerId) {
            return $player->getId() == $playerId;
        });
        if (!$exists) {
            throw new NotFoundHttpException();
        }

        return GameResource::make($game)->additional(['userId' => $request->input('userId')]);
    }

    /**
     * @param JoinGameRequest $request
     * @param string $id
     * @throws GameException
     */
    public function join(JoinGameRequest $request, string $id)
    {
        $game = Game::get($id);
        $game->getPlayers()->add(new Player(
            $request->input('userId'),
            $request->input('name'),
            $game->getConfig()->getInitialMoney()
        ));
        $game->save();
    }

    public function ready(ReadyRequest $request, string $id)
    {
        $game = Game::get($id);
        $game->getPlayers()
            ->getById($request->input('userId'))
            ->setIsReady($request->input('value'));
        $game->save();
    }

    /**
     * @param StartGameRequest $request
     * @param string $id
     * @throws GameException
     */
    public function start(StartGameRequest $request, string $id)
    {
        $game = Game::get($id);
        if ($game->getCreatorId() != $request->input('userId')) {
            throw new AccessDeniedHttpException('Only creator of the game can start game');
        }
        $game->start();
    }

    /**
     * @param UpdateGameRequest $request
     * @param string $id
     * @return GameResource
     * @throws GameException
     */
    public function update(UpdateGameRequest $request, string $id)
    {
        $game = Game::get($id);
        // TODO: add timeout logic
        if ($game->getPlayers()->getActivePlayer()->getId() != $request->input('userId')) {
            throw new GameException('It is not you turn');
        }
        $action = ActionFactory::get($request);
        $action->updateGame($game);

        $game->getDeal()->onAfterUpdate();

        $game->save();

        return GameResource::make($game)->additional(['userId' => $request->input('userId')]);
    }
}
