<?php

namespace Tests\Feature\Flow;

use App\Entities\Database\Game\Deal;

// TODO: finish this flow & make full flow for 2 players
class BasicFlow_2_Players_Test extends FlowTest
{
    public function testFlow()
    {
        $this->create();
        $this->join(2);
        $this->setReady();
        $this->start();
        $this->preFlop();
        $this->flop();

        $content = $this->getGame();
    }

    /*
     * player 1 - call
     * player 2 - raise
     * player 1 - call
     */
    private function preFlop(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_PREFLOP);
        $this->assertCount(0, $game['communityCards']);
        $this->assertTrue($game['pot'] == 15);
        $this->assertTrue($game['players'][1]['isActive']);

        $this->assertTrue($game['players'][1]['money'] == 495);
        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][1]['money'] == 490);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'bet',
            'value' => 20,
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['money'] == 470);
        $this->assertTrue($game['players'][2]['bet'] == 30);
        $this->assertTrue($game['pot'] == 40);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[1],
            'action' => 'call'
        ]);

        // round ends
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][1]['money'] == 470);
        // bet is 0 since new round starts
        $this->assertTrue($game['players'][1]['bet'] == 0);
        $this->assertTrue($game['players'][2]['bet'] == 0);
    }

    public function flop()
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_FLOP);
        $this->assertCount(3, $game['communityCards']);
        $this->assertTrue($game['pot'] == 60);

        // TODO: check that player 2 should be active
        $this->assertTrue($game['players'][2]['isActive']);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[2],
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['money'] == 470);
        $this->assertTrue($game['players'][2]['isFolded'] == true);
    }
}
