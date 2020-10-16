<?php

namespace Tests\Feature\Flow;

use App\Entities\Database\Game\Deal;

// game finished after 2 of 3 players make fold. winner is the last player
class AllPlayersFolded_3_Players_Test extends FlowTest
{
    public function testFlow()
    {
        $this->create();
        $this->join(3);
        $this->setReady();
        $this->start();
        $this->preFlop();

        $content = $this->getGame();
    }

    /*
     * player 2 - fold
     * player 3 - fold
     *
     * player 1 - winner
     */
    private function preFlop(): void
    {
        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_PREFLOP);
        $this->assertCount(0, $game['communityCards']);
        $this->assertTrue($game['pot'] == 15);
        $this->assertTrue($game['players'][1]['isActive']);

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
            'action' => 'fold'
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][2]['isFolded'] == true);
        $this->assertTrue($game['players'][2]['money'] == 495);
        $this->assertTrue($game['players'][2]['bet'] == 5);

        $this->assertTrue($game['deal']['status'] == Deal::STATUS_END);
        $this->assertNotEmpty($game['deal']['winners']);
        $this->assertTrue($game['players'][3]['money'] == 505);

        $game = $this->getGame();
        $this->assertTrue($game['deal']['status'] == Deal::STATUS_PREFLOP);
    }
}
