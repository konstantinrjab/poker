<?php

namespace Tests\Feature\Flow;

use App\Entities\Database\Game\Deal;

class BasicFlowTest extends FlowTest
{
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
        $this->newDealStarted();

        $content = $this->getGame();
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

        // check call value displayed
        foreach ($game['players'][5]['availableActions'] as $action) {
            if ($action['type'] == 'call') {
                $callIsAvailable = true;
                $this->assertTrue($action['value'] == 10);
            }
        }
        $this->assertNotEmpty($callIsAvailable);

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
     *
     * player 2 - check
     * player 3 - bet 20
     * player 5 - call
     * player 1 - call
     * player 2 - call
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

        $this->assertTrue($game['players'][2]['isActive']);

        foreach ($game['players'] as $playerNumber => $player) {
            $this->assertTrue($game['players'][$playerNumber]['bet'] == 0);
        }

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'check'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['money'] == 490);
        $this->assertTrue($game['pot'] == 40);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[3],
            'action' => 'bet',
            'value' => 15
        ]);
        // min bet is (bigBlind * 2) = 20
        $this->assertTrue($response->getStatusCode() == 400);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[3],
            'action' => 'bet',
            'value' => 20
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 60);
        $this->assertTrue($game['players'][3]['money'] == 470);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 80);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 100);
        $this->assertTrue($game['players'][1]['money'] == 470);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 120);
        $this->assertTrue($game['players'][2]['money'] == 470);
    }

    /*
     * player 4 - folded
     *
     * player 2 - bet 40
     * player 3 - fold
     * player 5 - call
     * player 1 - call
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

        $this->assertTrue($game['players'][2]['isActive']);
        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'bet',
            'value' => 40
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 160);
        $this->assertTrue($game['players'][2]['money'] == 430);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[3],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][3]['isFolded'] == true);
        $this->assertTrue($game['players'][3]['money'] == 470);
        $this->assertTrue($game['players'][3]['bet'] == 0);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 200);
        $this->assertTrue($game['players'][5]['money'] == 430);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 240);
        $this->assertTrue($game['players'][1]['money'] == 430);
    }

    /*
     * player 3 - folded
     * player 4 - folded
     *
     * player 2 - check
     * player 5 - bet 40
     * player 1 - bet 60
     * player 2 - fold
     * player 5 - call
     */
    private function river(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_RIVER);
        $this->assertCount(5, $game['communityCards']);
        $this->assertTrue($game['players'][1]['money'] == 430);
        $this->assertTrue($game['players'][2]['money'] == 430);
        $this->assertTrue($game['players'][3]['money'] == 470);
        $this->assertTrue($game['players'][4]['money'] == 500);
        $this->assertTrue($game['players'][5]['money'] == 430);
        $this->assertTrue($game['pot'] == 240);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'check'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['money'] == 430);
        $this->assertTrue($game['pot'] == 240);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'bet',
            'value' => 40
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 280);
        $this->assertTrue($game['players'][5]['money'] == 390);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'bet',
            'value' => 60
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 340);
        $this->assertTrue($game['players'][1]['money'] == 370);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['isFolded'] == true);
        $this->assertTrue($game['players'][2]['money'] == 430);
        $this->assertTrue($game['players'][2]['bet'] == 0);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'call'
        ]);

        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_END);
        $this->assertNotEmpty($game['deal']['winners']);

        $totalMoney = 0;
        foreach ($game['players'] as $player) {
            $totalMoney += $player['money'];
        }
        $this->assertTrue($totalMoney == 5 * 500);
    }

    private function newDealStarted(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_PREFLOP);
        $this->assertCount(0, $game['communityCards']);

        $totalMoney = $game['pot'];
        foreach ($game['players'] as $player) {
            $totalMoney += $player['money'];
        }
        $this->assertTrue($totalMoney == 5 * 500);
    }
}
