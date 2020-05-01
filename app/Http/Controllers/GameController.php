<?php

namespace App\Http\Controllers;

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
        $game = new Game(1);
        $game->addPlayer(new Player(1));
        $game->addPlayer(new Player(2));
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
     * @return \Illuminate\Http\Response
     */
    public function store(CreateGameRequest $request)
    {
        $game = new Game(Auth::id());
        $game->addPlayer(new Player(Auth::id()));
        $game->save();

        return response([
            'gameId' => $game->getId()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $resource = new GameResource(Game::get($id));
        return $resource;
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
        $game->getPlayers()->add(Auth::id());
        $game->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
