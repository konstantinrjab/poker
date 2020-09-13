<?php

namespace Tests\Feature;

use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;
use Exception;

abstract class FlowTest extends TestCase
{
    protected array $playersIds = [];
    protected string $gameId;

    abstract public function testFlow();

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(
            ThrottleRequests::class
        );
    }

    protected function create(): void
    {
        $response = $this->post('/api/register', [
            'name' => 'creatorName',
        ]);
        if (!isset($response->json()['data']['id'])) {
            throw new Exception('Invalid game state: ' . var_export($response->json()['data'], true));
        }
        $response = $this->post('/api/games', [
            'userId' => $response->json()['data']['id'],
            'minPlayers' => 3,
            'maxPlayers' => 5,
            'bigBlind' => 10,
            'smallBlind' => 5,
            'initialMoney' => 500,
        ]);
        if (!isset($response->json()['data']['id'])) {
            throw new Exception('Invalid game state: ' . var_export($response->json()['data'], true));
        }
        $this->gameId = $response->json()['data']['id'];
        $this->assertIsString($this->gameId);
        $this->playersIds[1] = $response->json()['data']['players'][0]['id'];
    }

    protected function getGameFromResponse($response): array
    {
        return $this->setPlayersOffset($response->json()['data']);
    }

    protected function getGame(): array
    {
        $game = $this->get('/api/games/' . $this->gameId . '?userId=' . $this->playersIds[1])->json()['data'];

        return $this->setPlayersOffset($game);
    }

    protected function setPlayersOffset(array $game): array
    {
        if (!isset($game['players'])) {
            throw new Exception('Invalid game state: ' . var_export($game, true));
        }
        $players = array_combine(
            array_map(function ($key) {
                return ++$key;
            }, array_keys($game['players'])),
            $game['players']
        );
        $game['players'] = $players;

        return $game;
    }

    protected function join(int $playersCount = 5): void
    {
        for ($userNumber = 2; $userNumber <= $playersCount; $userNumber++) {
            $response = $this->post('/api/register', [
                'name' => 'player_' . $userNumber,
            ]);
            $this->playersIds[$userNumber] = $response->json()['data']['id'];
            $response = $this->put('/api/games/' . $this->gameId . '/join', [
                'userId' => $response->json()['data']['id'],
            ]);
            $this->assertTrue($response->status() == 200);
        }
    }

    protected function setReady(): void
    {
        for ($userNumber = 1; $userNumber <= count($this->playersIds); $userNumber++) {
            $response = $this->put('/api/games/' . $this->gameId . '/ready', [
                'userId' => $this->playersIds[$userNumber],
                'value' => true
            ]);
            $this->assertTrue($response->status() == 200);
        }
    }

    protected function start(): void
    {
        $response = $this->put('/api/games/' . $this->gameId . '/start', [
            'userId' => 'incorrectUserId'
        ]);
        $this->get('/api/games/' . $this->gameId);
        $this->assertTrue($response->status() == 400);

        $response = $this->put('/api/games/' . $this->gameId . '/start', [
            'userId' => $this->playersIds[1]
        ]);
        $this->assertTrue($response->status() == 200);
    }
}
