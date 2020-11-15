<?php

namespace Tests\Feature;

use Tests\TestCase;
use Exception;

class CreateGameTest extends TestCase
{
    public function testInitialMoneyIsNotEnough()
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
            'initialMoney' => 99,
        ]);
        $this->assertTrue($response->getStatusCode() == 400);
    }

    public function testInitialMoneyIsEnough()
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
    }

    public function testBigBlindNotEnough()
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
            'bigBlind' => 6,
            'smallBlind' => 5,
            'initialMoney' => 99,
        ]);
        $this->assertTrue($response->getStatusCode() == 400);
    }

    public function testBigBlindIsEnough()
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
    }
}
