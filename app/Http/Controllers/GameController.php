<?php

namespace App\Http\Controllers;

use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Requests\StartGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Action;
use App\Models\Game;
use App\Models\Player;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class GameController extends Controller
{
    /**
     * @todo remove this method
     */
    public function index()
    {
        $game = new Game(uniqid());
        $game->addPlayer(new Player(uniqid()));
        $game->addPlayer(new Player(uniqid()));
        $game->addPlayer(new Player(uniqid()));
        $game->addPlayer(new Player(uniqid()));
        $game->start();
        $winners = $game->getRound()->getWinners();
        $winners->dd();
        return response();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateGameRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateGameRequest $request)
    {
        $game = new Game($request->get('userId'));
        $game->addPlayer(new Player($request->get('userId')));
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
        $game->getPlayers()->add(new Player($request->get('userId')));
        $game->save();
    }

    public function start(StartGameRequest $request, string $id)
    {
        $game = Game::get($id);
        if ($game->getCreatorId() != $request->get('userId')) {
            throw new AccessDeniedException();
        }
        try {
            $game->start();
        } catch (GameException $e) {
            return response($e->getMessage());
        }
        return response();
    }

    public function update(UpdateGameRequest $request, string $id)
    {
        $game = Game::get($id);
        if ($game->getRound()->getActivePlayer()->getId() != $request->get('userId')) {
            throw new GameException('It is not you turn');
        }
        $action = new Action($request);
        $action->updateRound($game->getRound());
        $game->getRound()->passTurn();
        $game->save();
        return GameResource::make($game);
    }
}
