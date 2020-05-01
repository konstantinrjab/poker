<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PokerController extends Controller
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $game = new Game(1);
        Redis::set('game:' . $game->getId(), serialize($game));
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
        $game = Redis::get('game:' . $id);
        $resource = new GameResource(unserialize($game));
        return $resource;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
