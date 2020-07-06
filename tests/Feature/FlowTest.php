<?php

namespace Tests\Feature;

use Tests\TestCase;

class FlowTest extends TestCase
{
    private array $playersIds = [];

    public function testFlow()
    {
        $gameId = $this->create();
        $this->join($gameId);
        $this->setReady($gameId);
        $this->start($gameId);
        $this->preFlop($gameId);
        $this->flop($gameId);
//        $this->turn($gameId);
        $response = $this->get('/api/games/' . $gameId . '?userId=' . $this->playersIds[0]);
        $this->assertTrue($response->status() == 200);

        $content = $response->json();
    }

    private function create(): string
    {
        $response = $this->post('/api/register', [
            'name' => 'creatorName',
        ]);
        $response = $this->post('/api/games', [
            'userId' => $response->json()['data']['id'],
            'maxPlayers' => 5,
            'bigBlind' => 10,
            'smallBlind' => 5,
            'initialMoney' => 500,
        ]);
        $gameId = $response->json()['data']['id'];
        $this->assertIsString($gameId);
        $this->playersIds[0] = $response->json()['data']['players'][0]['id'];

        return $gameId;
    }

    private function join(string $gameId): void
    {
        for ($userNumber = 1; $userNumber <= 4; $userNumber++) {
            $response = $this->post('/api/register', [
                'name' => 'player_' . $userNumber,
            ]);
            $this->playersIds[$userNumber] = $response->json()['data']['id'];
            $response = $this->put('/api/games/' . $gameId . '/join', [
                'userId' => $response->json()['data']['id'],
            ]);
            $this->assertTrue($response->status() == 200);
        }
    }

    private function setReady(string $gameId): void
    {
        for ($userNumber = 0; $userNumber <= 4; $userNumber++) {
            $response = $this->put('/api/games/' . $gameId . '/ready', [
                'userId' => $this->playersIds[$userNumber],
                'value' => true
            ]);
            $this->assertTrue($response->status() == 200);
        }
    }

    private function start(string $gameId): void
    {
        $response = $this->put('/api/games/' . $gameId . '/start', [
            'userId' => 'incorrectUserId'
        ]);
        $this->get('/api/games/' . $gameId);
        $this->assertTrue($response->status() == 403);
        $response = $this->put('/api/games/' . $gameId . '/start', [
            'userId' => $this->playersIds[0]
        ]);
        $this->assertTrue($response->status() == 200);
    }

    /*
     * player 4 - fold
     * player 5 - call
     * player 1 - call
     * player 2 - call
     */
    private function preFlop(string $gameId): void
    {
        $game = $this->get('/api/games/' . $gameId . '?userId=' . $this->playersIds[0])->json()['data'];
        $this->assertCount(0, $game['communityCards']);
        $this->assertTrue($game['pot'] == 15);
        $this->assertTrue($game['players'][3]['isActive']);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[3],
            'action' => 'fold'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['players'][3]['isFolded'] == true);
        $this->assertTrue($game['players'][3]['money'] == 500);

        $this->assertTrue($response->json()['data']['players'][4]['money'] == 500);
        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['players'][4]['money'] == 490);
        $this->assertTrue($game['players'][4]['bet'] == 10);
        $this->assertTrue($game['pot'] == 25);

        $this->assertTrue($response->json()['data']['players'][0]['money'] == 500);
        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[0],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['players'][0]['money'] == 490);
        $this->assertTrue($game['players'][0]['bet'] == 10);

        $this->assertTrue($game['pot'] == 35);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);
        // round ends
    }

    /*
     * player 5 - call
     * player 1 - bet (big blind 10 + raise 40)
     * player 2 - call
     * player 5 - call
     */
    private function flop(string $gameId): void
    {
        $game = $this->get('/api/games/' . $gameId . '?userId=' . $this->playersIds[0])->json()['data'];
        $this->assertCount(3, $game['communityCards']);
        // big and small blind auto bets
        $this->assertTrue($game['players'][1]['money'] == 485);
        $this->assertTrue($game['players'][2]['money'] == 480);

        $this->assertTrue($game['pot'] == 55);
        $this->assertTrue($game['players'][4]['isActive']);

        $this->assertTrue($game['players'][0]['bet'] == 0);
        $this->assertTrue($game['players'][1]['bet'] == 5);
        $this->assertTrue($game['players'][2]['bet'] == 10);
        $this->assertTrue($game['players'][3]['bet'] == 0);
        $this->assertTrue($game['players'][4]['bet'] == 0);

        $this->assertTrue($game['players'][4]['money'] == 490);
        // player should fold or call, check is not available
        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'check'
        ]);
        $this->assertTrue($response->getStatusCode() == 400);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['players'][4]['money'] == 480);
        $this->assertTrue($game['pot'] == 65);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[0],
            'action' => 'bet',
            'value' => 50
        ]);
        $game = $response->json()['data'];
        // fix round max bet value for this case
        $this->assertTrue($game['pot'] == 115);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['pot'] == 150);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['pot'] == 180);
    }

    private function turn(string $gameId): void
    {
        $game = $this->get('/api/games/' . $gameId . '?userId=testUserId3')->json()['data'];
        $this->assertCount(4, $game['communityCards']);
    }
}
