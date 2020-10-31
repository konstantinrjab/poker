<?php

namespace Tests\Feature;

use Tests\TestCase;

class StartGameTest extends TestCase
{
    public function testStartNotCreator()
    {
        $creatorId = $this->post('/api/register', [
            'name' => 'creatorId',
        ])->json()['data']['id'];
        $playerId = $this->post('/api/register', [
            'name' => 'playerId',
        ])->json()['data']['id'];
        $response = $this->post('/api/games', [
            'userId' => $creatorId,
            'minPlayers' => 2,
            'maxPlayers' => 5,
            'bigBlind' => 10,
            'smallBlind' => 5,
            'initialMoney' => 100,
        ]);
        $this->assertTrue($response->getStatusCode() == 200);
        $gameId = $response->json()['data']['id'];

        $response = $this->put('/api/games/' . $gameId . '/join', [
            'userId' => $playerId,
        ]);
        $this->assertTrue($response->status() == 200);

        $response = $this->put('/api/games/' . $gameId . '/start', [
            'userId' => 'incorrectUserId'
        ]);
        $this->assertTrue($response->status() == 401);

        $response = $this->put('/api/games/' . $gameId  . '/start', [
            'userId' => $playerId
        ]);
        $this->assertTrue($response->status() == 403);
    }
}
