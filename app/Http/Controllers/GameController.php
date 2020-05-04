<?php

namespace App\Http\Controllers;

use App\Exceptions\GameException;
use App\Http\Requests\CreateGameRequest;
use App\Http\Requests\JoinGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Player;
use Auth;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
        $game->addPlayer(new Player(Auth::id()));
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

    /**
     * Update the specified resource in storage.
     *
     * @param JoinGameRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function join(JoinGameRequest $request, $id)
    {
        $game = Game::get($id);
        $game->getPlayers()->add($request->get('userId'));
        $game->save();
        return response();
    }

    public function start($id)
    {
        $game = Game::get($id);
        try {
            $game->start();
        } catch (GameException $e) {
            return response($e->getMessage());
        }
        return response();
    }
}
