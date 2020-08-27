<?php

namespace Tests\Feature;

use App\Models\Deal;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class FlowTest extends TestCase
{
    private array $playersIds = [];
    private string $gameId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(
            ThrottleRequests::class
        );
    }

    public function testFlow()
    {
        $this->create();
        $this->join();
        $this->setReady();
        $this->start();
        $this->preFlop();
        $this->flop();
        $this->turn();
        $this->river();

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
        // TODO: player3 is BB. he can raise his bet

        // round ends
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['money'] == 490);
        // bet is 0 since new round starts
        $this->assertTrue($game['players'][2]['bet'] == 0);
        $this->assertTrue($game['pot'] == 40);
    }

    /*
     * player 4 - folded
     * player 5 - check
     * player 1 - bet 20
     * player 2 - call
     * player 3 - call
     * player 5 - call
     */
    private function flop(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_FLOP);
        $this->assertCount(3, $game['communityCards']);
        $this->assertTrue($game['players'][1]['money'] == 490);
        $this->assertTrue($game['players'][2]['money'] == 490);
        $this->assertTrue($game['players'][3]['money'] == 490);
        $this->assertTrue($game['players'][4]['money'] == 500);
        $this->assertTrue($game['players'][5]['money'] == 490);

        $this->assertTrue($game['pot'] == 40);
        // since player 4 folded -> player 5 is active
        $this->assertTrue($game['players'][5]['isActive']);

        foreach ($game['players'] as $playerNumber => $player) {
            $this->assertTrue($game['players'][$playerNumber]['bet'] == 0);
        }

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'check'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][5]['money'] == 490);
        $this->assertTrue($game['pot'] == 40);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'bet',
            'value' => 15
        ]);
        // min bet is (bigBlind * 2) = 20
        $this->assertTrue($response->getStatusCode() == 400);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'bet',
            'value' => 20
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 60);
        $this->assertTrue($game['players'][1]['money'] == 470);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 80);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[3],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 100);
        $this->assertTrue($game['players'][1]['money'] == 470);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 120);
        $this->assertTrue($game['players'][1]['money'] == 470);
    }

    /*
     * player 4 - folded
     * player 5 - bet 40
     * player 1 - fold
     * player 2 - call
     * player 3 - call
     */
    private function turn(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_TURN);
        $this->assertCount(4, $game['communityCards']);
        $this->assertTrue($game['players'][1]['money'] == 470);
        $this->assertTrue($game['players'][2]['money'] == 470);
        $this->assertTrue($game['players'][3]['money'] == 470);
        $this->assertTrue($game['players'][4]['money'] == 500);
        $this->assertTrue($game['players'][5]['money'] == 470);
        $this->assertTrue($game['pot'] == 120);

        $this->assertTrue($game['players'][5]['isActive']);
        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'bet',
            'value' => 40
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 160);
        $this->assertTrue($game['players'][5]['money'] == 430);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][1]['isFolded'] == true);
        $this->assertTrue($game['players'][1]['money'] == 470);
        $this->assertTrue($game['players'][1]['bet'] == 0);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 200);
        $this->assertTrue($game['players'][2]['money'] == 430);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[3],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 240);
        $this->assertTrue($game['players'][3]['money'] == 430);
    }

    /*
     * player 4 - folded
     * player 5 - check
     * player 1 - folded
     * player 2 - bet 40
     * player 3 - bet 60
     * player 5 - fold
     * player 2 - call
     */
    private function river(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_RIVER);
        $this->assertCount(5, $game['communityCards']);
        $this->assertTrue($game['players'][1]['money'] == 470);
        $this->assertTrue($game['players'][2]['money'] == 430);
        $this->assertTrue($game['players'][3]['money'] == 430);
        $this->assertTrue($game['players'][4]['money'] == 500);
        $this->assertTrue($game['players'][5]['money'] == 430);
        $this->assertTrue($game['pot'] == 240);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'check'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][5]['money'] == 430);
        $this->assertTrue($game['pot'] == 240);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'bet',
            'value' => 40
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 280);
        $this->assertTrue($game['players'][2]['money'] == 390);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[3],
            'action' => 'bet',
            'value' => 60
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 340);
        $this->assertTrue($game['players'][3]['money'] == 370);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][5]['isFolded'] == true);
        $this->assertTrue($game['players'][5]['money'] == 430);
        $this->assertTrue($game['players'][5]['bet'] == 0);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'call'
        ]);
        // game ends
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_END);
        $this->assertNotEmpty($game['deal']['winners']);

//        $this->assertTrue($game['pot'] == 360);
//        $this->assertTrue($game['players'][2]['money'] == 370);
//        $this->assertTrue($game['players'][2]['bet'] == 60);
    }
}
