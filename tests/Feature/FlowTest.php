<?php

namespace Tests\Feature;

use Tests\TestCase;

class FlowTest extends TestCase
{
    const CREATOR_ID = 'testUserId1';

    public function testFlow()
    {
        $gameId = $this->create();
        $this->join($gameId);
        $this->setReady($gameId);
        $this->start($gameId);
        $this->preFlop($gameId);
        $this->flop($gameId);
        $response = $this->get('/api/games/' . $gameId)->json();
    }

    private function create(): string
    {
        $response = $this->post('/api/games', [
            'userId' => self::CREATOR_ID,
            'name' => 'creator',
            'bigBlind' => 10,
            'smallBlind' => 5,
            'initialMoney' => 500,
        ]);
        $gameId = $response->json()['gameId'];
        $this->assertIsString($gameId);

        return $gameId;
    }

    private function join(string $gameId)
    {
        for ($userNumber = 2; $userNumber <= 5; $userNumber++) {
            $response = $this->put('/api/games/' . $gameId . '/join', [
                'userId' => 'testUserId' . $userNumber,
                'name' => 'player_' . $userNumber
            ]);
            $this->assertTrue($response->status() == 200);
        }
    }

    private function setReady(string $gameId)
    {
        $response = $this->put('/api/games/' . $gameId. '/ready', [
            'userId' => self::CREATOR_ID,
            'value' => true
        ]);
        $this->assertTrue($response->status() == 200);

        for ($userNumber = 2; $userNumber <= 5; $userNumber++) {
            $response = $this->put('/api/games/' . $gameId . '/ready', [
                'userId' => 'testUserId' . $userNumber,
                'value' => true
            ]);
            $this->assertTrue($response->status() == 200);
        }
    }

    private function start(string $gameId)
    {
        $response = $this->put('/api/games/' . $gameId . '/start', [
            'userId' => 'incorrectUserId'
        ]);
        $this->get('/api/games/' . $gameId);
        $this->assertTrue($response->status() == 403);
        $response = $this->put('/api/games/' . $gameId . '/start', [
            'userId' => self::CREATOR_ID
        ]);
        $this->assertTrue($response->status() == 200);
    }

    private function preFlop(string $gameId)
    {
        $game = $this->get('/api/games/' . $gameId)->json()['data'];
        $this->assertCount(0, $game['communityCards']);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => 'testUserId4',
            'action' => 'fold'
        ]);
        $this->assertTrue($response->json()['data']['players'][3]['isFolded'] == true);
        $this->assertTrue($response->json()['data']['players'][3]['money'] == 500);

        $this->assertTrue($response->json()['data']['players'][4]['money'] == 500);
        $response = $this->put('/api/games/' . $gameId, [
            'userId' => 'testUserId5',
            'action' => 'call'
        ]);
        $this->assertTrue($response->json()['data']['players'][4]['money'] == 490);
        $this->assertTrue($response->json()['data']['players'][4]['bet'] == 10);

        $this->assertTrue($response->json()['data']['players'][0]['money'] == 500);
        $response = $this->put('/api/games/' . $gameId, [
            'userId' => self::CREATOR_ID,
            'action' => 'call'
        ]);
        $this->assertTrue($response->json()['data']['players'][0]['money'] == 490);
        $this->assertTrue($response->json()['data']['players'][0]['bet'] == 10);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => 'testUserId2',
            'action' => 'call'
        ]);
        $this->assertTrue($response->json()['data']['players'][0]['money'] == 490);
    }

    private function flop(string $gameId)
    {
        $game = $this->get('/api/games/' . $gameId)->json()['data'];
        $this->assertCount(3, $game['communityCards']);
        $this->assertTrue($game['players'][4]['isActive']);

        // round ends, all bets resets to zero
        $this->assertTrue($game['players'][0]['bet'] == 0);
        $this->assertTrue($game['pot'] == 40);
    }
}