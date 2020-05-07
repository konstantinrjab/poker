<?php

namespace App\Http\Controllers;

use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Requests\StartGameRequest;
use App\Http\Resources\GameResource;
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
        if (!$game->isReadyToStart()) {
            return response('Cannot start game')->send();
        }
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
        $game = new Game($request->get('creatorId'));
        $game->addPlayer(new Player($request->get('creatorId')));
        $game->save();

        return response()->json([
            'gameId' => $game->getId()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $resource = new GameResource(Game::get($id));
        return response($resource);
    }

    public function join(JoinGameRequest $request, $id)
    {
        $game = Game::get($id);
        $game->getPlayers()->add($request->get('userId'));
        $game->save();
    }

    public function start(StartGameRequest $request, $id)
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
}
