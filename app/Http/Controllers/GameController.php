<?php

namespace App\Http\Controllers;

use App\Events\GameUpdated;
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
use Event;
use Str;

class GameController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param CreateGameRequest $request
     * @return GameResource
     */
    public function store(CreateGameRequest $request)
    {
        $config = new GameConfig(
            $request->input('smallBlind'),
            $request->input('bigBlind'),
            $request->input('initialMoney'),
            $request->input('maxPlayers')
        );
        $player = new Player(Str::uuid(), $request->input('name'), $config->getInitialMoney());
        $game = new Game($config, $player->getId());
        $game->getPlayers()->add($player);
        $game->save();

        $this->configureApp($game, $player->getId());

        return GameResource::make($game);
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

        $this->configureApp($game, $request->input('userId'));
        Event::dispatch(GameUpdated::NAME, $game);

        return GameResource::make($game);
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

        if ($game->getPlayers()->count() >= $game->getConfig()->getMaxPlayersCount()) {
            throw new GameException('Game is full');
        }

        $player = new Player(Str::uuid(), $request->input('name'), $game->getConfig()->getInitialMoney());
        $game->getPlayers()->add($player);
        $game->save();

        $this->configureApp($game, $player->getId());
        Event::dispatch(GameUpdated::NAME, $game);

        return GameResource::make($game);
    }

    public function ready(string $id, ReadyRequest $request)
    {
        $game = Game::get($id);
        $game->getPlayers()
            ->getById($request->input('userId'))
            ->setIsReady($request->input('value'));
        $game->save();

        $this->configureApp($game, $request->input('userId'));
        Event::dispatch(GameUpdated::NAME, $game);

        return GameResource::make($game);
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
        if ($game->getCreatorId() != $request->input('userId')) {
            throw new AccessDeniedHttpException('Only creator of the game can start game');
        }
        $game->start();

        $this->configureApp($game, $request->input('userId'));
        Event::dispatch(GameUpdated::NAME, $game);

        return GameResource::make($game);
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
        if ($game->getPlayers()->getActivePlayer()->getId() != $request->input('userId')) {
            throw new GameException('It is not you turn');
        }
        $action = ActionFactory::get($request);
        $action->updateGame($game);

        $game->getDeal()->onAfterUpdate();

        $game->save();

        $this->configureApp($game, $request->input('userId'));
        Event::dispatch(GameUpdated::NAME, $game);

        return GameResource::make($game);
    }

    // TODO: move it to service provider if possible
    private function configureApp(Game $game, string $userId)
    {
        app()->instance('game.instance', $game);
        app()->instance('game.userId', $userId);
    }
}
