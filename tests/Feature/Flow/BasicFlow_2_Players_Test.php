<?php

namespace Tests\Feature\Flow;

use App\Entities\Database\Game\Deal;

class BasicFlow_2_Players_Test extends FlowTest
{
    public function testFlow()
    {
        $this->create();
        $this->join(2);
        $this->setReady();
        $this->start();
        $this->preFlop();

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

        // TODO: Round round here. But should it?

//        $this->assertTrue($game['players'][1]['bet'] == 10);
//        $this->assertTrue($game['pot'] == 20);
//
//        $this->assertTrue($game['players'][2]['money'] == 490);
//        $response = $this->put('/api/games/' . $this->gameId, [
//            'userId' => $this->playersIds[1],
//            'action' => 'bet',
//            'value' => 15,
//        ]);
//        $game = $this->getGameFromResponse($response);
//        $this->assertTrue($game['players'][1]['money'] == 475);
//        $this->assertTrue($game['players'][1]['bet'] == 25);
//
//        $this->assertTrue($game['pot'] == 35);
//
//        $response = $this->put('/api/games/' . $this->gameId, [
//            'userId' => $this->playersIds[1],
//            'action' => 'call'
//        ]);
//        $this->assertTrue($game['players'][1]['money'] == 475);
//        $this->assertTrue($game['players'][1]['bet'] == 25);
//
//        $this->assertTrue($game['pot'] == 50);
//
//        // round ends
//        $game = $this->getGameFromResponse($response);
//        $this->assertTrue($game['players'][1]['money'] == 475);
//        // bet is 0 since new round starts
//        $this->assertTrue($game['players'][2]['bet'] == 0);
//        $this->assertTrue($game['pot'] == 40);
    }
}
