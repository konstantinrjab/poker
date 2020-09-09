<?php

namespace Tests\Feature;

use App\Entities\Database\Game\Deal;

// new round starts when everyone makes check
class AllPlayersCheckedTest extends FlowTest
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
    * player 3 - check
    * player 5 - check
    * player 1 - check
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
            'action' => 'check',
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 40);
        $this->assertTrue($game['players'][3]['money'] == 490);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'check'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 40);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'check'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['pot'] == 40);
        $this->assertTrue($game['players'][1]['money'] == 490);
    }

    private function turn(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_TURN);
        $this->assertCount(4, $game['communityCards']);
    }
}
