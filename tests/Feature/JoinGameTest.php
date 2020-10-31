<?php

namespace Tests\Feature;

use Tests\TestCase;
use Exception;

class JoinGameTest extends TestCase
{
    public function testJoinUnauthenticated()
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
            'initialMoney' => 100,
        ]);
        $this->assertTrue($response->getStatusCode() == 200);

        $response = $this->put('/api/games/' . $response->json()['data']['id'] . '/join', [
            'userId' => 'idNotExists',
        ]);
        $this->assertTrue($response->status() == 401);
    }
}
