<?php

namespace Tests\Feature;

use App\Models\Deal;
use Tests\TestCase;

class FlowTest extends TestCase
{
    private array $playersIds = [];
    private string $gameId;

    public function testFlow()
    {
        $this->create();
        $this->join();
        $this->setReady();
        $this->start();
        $this->preFlop();
        $this->flop();
//        $this->turn($gameId);

        $content = $this->getGame();
    }

    private function create(): void
    {
        $response = $this->post('/api/register', [
            'name' => 'creatorName',
        ]);
        $response = $this->post('/api/games', [
            'userId' => $response->json()['data']['id'],
            'minPlayers' => 2,
            'maxPlayers' => 5,
            'bigBlind' => 10,
            'smallBlind' => 5,
            'initialMoney' => 500,
        ]);
        $this->gameId = $response->json()['data']['id'];
        $this->assertIsString($this->gameId);
        $this->playersIds[1] = $response->json()['data']['players'][0]['id'];
    }

    private function getGameFromResponse($response): array
    {
        return $this->setPlayersOffset($response->json()['data']);
    }

    private function getGame(): array
    {
        $game = $this->get('/api/games/' . $this->gameId . '?userId=' . $this->playersIds[1])->json()['data'];

        return $this->setPlayersOffset($game);
    }

    private function setPlayersOffset(array $game): array
    {
        $players = array_combine(
            array_map(function ($key) {
                return ++$key;
            }, array_keys($game['players'])),
            $game['players']
        );
        $game['players'] = $players;

        return $game;
    }

    private function join(): void
    {
        for ($userNumber = 2; $userNumber <= 5; $userNumber++) {
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

    private function setReady(): void
    {
        for ($userNumber = 1; $userNumber <= 5; $userNumber++) {
            $response = $this->put('/api/games/' . $this->gameId . '/ready', [
                'userId' => $this->playersIds[$userNumber],
                'value' => true
            ]);
            $this->assertTrue($response->status() == 200);
        }
    }

    private function start(): void
    {
        $response = $this->put('/api/games/' . $this->gameId . '/start', [
            'userId' => 'incorrectUserId'
        ]);
        $this->get('/api/games/' . $this->gameId);
        $this->assertTrue($response->status() == 403);

        $response = $this->put('/api/games/' . $this->gameId . '/start', [
            'userId' => $this->playersIds[1]
        ]);
        $this->assertTrue($response->status() == 200);
    }

    /*
     * player 4 - fold
     * player 5 - call
     * player 1 - call
     * player 2 - call
     */
    private function preFlop(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_PREFLOP);
        $this->assertCount(0, $game['communityCards']);
        $this->assertTrue($game['pot'] == 15);
        $this->assertTrue($game['players'][4]['isActive']);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][4]['isFolded'] == true);
        $this->assertTrue($game['players'][4]['money'] == 500);
        $this->assertTrue($game['players'][4]['bet'] == 0);

        $this->assertTrue($game['players'][5]['money'] == 500);
        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][5]['money'] == 490);
        $this->assertTrue($game['players'][5]['bet'] == 10);
        $this->assertTrue($game['pot'] == 25);

        $this->assertTrue($game['players'][1]['money'] == 500);
        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][1]['money'] == 490);
        $this->assertTrue($game['players'][1]['bet'] == 10);

        $this->assertTrue($game['pot'] == 35);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'call'
        ]);
        // round ends
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['money'] == 490);
        // bet is 0 since new round starts
        $this->assertTrue($game['players'][2]['bet'] == 0);
        $this->assertTrue($game['pot'] == 40);
    }

    /*
     * player 5 - call
     * player 1 - bet (big blind 10 + raise 40)
     * player 2 - call
     * player 5 - call
     */
    private function flop(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_FLOP);
        $this->assertCount(3, $game['communityCards']);
        // big and small blind auto bets
        $this->assertTrue($game['players'][1]['money'] == 490);
        $this->assertTrue($game['players'][2]['money'] == 490);
        $this->assertTrue($game['players'][3]['money'] == 490);
        $this->assertTrue($game['players'][4]['money'] == 500);
        $this->assertTrue($game['players'][5]['money'] == 490);

        $this->assertTrue($game['pot'] == 40);
        $this->assertTrue($game['players'][5]['isActive']);

        foreach ($game['players'] as $playerNumber => $player) {
            $this->assertTrue($game['players'][$playerNumber]['bet'] == 0);
        }

        // player should fold or call, check is not available
        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'check'
        ]);
        $this->assertTrue($response->getStatusCode() == 400);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['players'][4]['money'] == 480);
        $this->assertTrue($game['pot'] == 65);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[0],
            'action' => 'bet',
            'value' => 50
        ]);
        $game = $response->json()['data'];
        // bet 50 big bling 10 - max bet is now 40
        $this->assertTrue($game['pot'] == 115);
        $this->assertTrue($game['players'][0]['money'] == 440);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['pot'] == 150);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['pot'] == 180);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'call'
        ]);
        $game = $response->json()['data'];
        $this->assertTrue($game['pot'] == 210);
    }

    private function turn(): void
    {
        $game = $this->get('/api/games/' . $this->gameId . '?userId=' . $this->playersIds[0])->json()['data'];
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_TURN);
        $this->assertCount(4, $game['communityCards']);
    }
}
