<?php

namespace Tests\Feature;

use App\Entities\Database\Game\Deal;

// game finished after 4 of 5 players make fold. winner is the last player
class AllPlayersFolded_5_Players_Test extends FlowTest
{
    public function testFlow()
    {
        $this->create();
        $this->join();
        $this->setReady();
        $this->start();
        $this->preFlop();
        $this->flop();

        $content = $this->getGame();
    }

    /*
     * player 4 - fold
     * player 5 - fold
     * player 1 - fold
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

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][5]['isFolded'] == true);
        $this->assertTrue($game['players'][5]['money'] == 500);
        $this->assertTrue($game['players'][5]['bet'] == 0);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][1]['isFolded'] == true);
        $this->assertTrue($game['players'][1]['money'] == 500);
        $this->assertTrue($game['players'][1]['bet'] == 0);

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
        $this->assertTrue($game['pot'] == 20);
    }

    /*
     * player 1 - folded
     * player 4 - folded
     * player 5 - folded
     *
     * player 2 - fold
     * player 3 - do nothing, winner
     */
    private function flop(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_FLOP);
        $this->assertCount(3, $game['communityCards']);
        $this->assertTrue($game['players'][1]['money'] == 500);
        $this->assertTrue($game['players'][2]['money'] == 490);
        $this->assertTrue($game['players'][3]['money'] == 490);
        $this->assertTrue($game['players'][4]['money'] == 500);
        $this->assertTrue($game['players'][5]['money'] == 500);

        $this->assertTrue($game['players'][2]['isActive']);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['money'] == 490);
        $this->assertTrue($game['players'][3]['money'] == 510);
        $this->assertTrue($game['pot'] == 0);
        $this->assertNotEmpty($game['deal']['winners']);

        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_CREATED);

        $response = $this->put('/api/games/' . $this->gameId . '/start', [
            'userId' => $this->playersIds[2]
        ]);
        $this->assertTrue($response->status() == 200);
    }
}
