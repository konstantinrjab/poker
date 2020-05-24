<?php

namespace App\Http\Controllers;

use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Requests\ReadyRequest;
use App\Http\Requests\StartGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Actions\Factories\ActionFactory;
use App\Models\Game;
use App\Models\Player;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GameController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateGameRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateGameRequest $request)
    {
        $game = new Game($request);
        $game->addPlayer(new Player(
            $request->get('userId'),
            $request->get('name'),
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
     * @return GameResource
     */
    public function show(string $id)
    {
        return GameResource::make(Game::get($id));
    }

    public function join(JoinGameRequest $request, string $id)
    {
        $game = Game::get($id);
        $game->getPlayers()->add(new Player(
            $request->get('userId'),
            $request->get('name'),
            $game->getConfig()->getInitialMoney()
        ));
        $game->save();
    }

    public function ready(ReadyRequest $request, string $id)
    {
        $game = Game::get($id);
        $game->getPlayers()->getById($request->input('userId'))->setIsReady($request->input('value'));
        $game->save();
    }

    public function start(StartGameRequest $request, string $id)
    {
        $game = Game::get($id);
        if ($game->getCreatorId() != $request->get('userId')) {
            throw new AccessDeniedHttpException('Only creator of the game can start game');
        }
        $game->start();
    }

    public function update(UpdateGameRequest $request, string $id)
    {
        $game = Game::get($id);
        // TODO: add timeout logic
        if ($game->getPlayers()->getActivePlayer()->getId() != $request->get('userId')) {
            throw new GameException('It is not you turn');
        }
        $action = ActionFactory::get($request);
        $action->updateGame($game);

        if (!$game->getDeal()->shouldEnd()) {
            $game->getDeal()->passTurn();
        } else {
            $game->getDeal()->end();
        }
        $game->save();
        return GameResource::make($game);
    }
}
