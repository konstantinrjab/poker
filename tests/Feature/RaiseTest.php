<?php

namespace Tests\Feature;

use App\Entities\Database\Game\Deal;

// raise amount checks
class RaiseTest extends FlowTest
{
    public function testFlow()
    {
        $this->create();
        $this->join();
        $this->setReady();
        $this->start();
        $this->preFlop();

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
            'action' => 'bet',
            'value' => 15
        ]);
        $this->assertTrue($response->getStatusCode() == 400);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[4],
            'action' => 'bet',
            'value' => 20
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][4]['money'] == 480);
        $this->assertTrue($game['pot'] == 35);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'bet',
            'value' => 10
        ]);
        $this->assertTrue($response->getStatusCode() == 400);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'bet',
            'value' => 20
        ]);
        $this->assertTrue($response->getStatusCode() == 400);

        $response = $this->put('/api/games/' . $this->gameId, [
            'userId' => $this->playersIds[5],
            'action' => 'bet',
            'value' => 30
        ]);
        $game = $this->getGameFromResponse($response);
        $this->assertTrue($game['players'][5]['money'] == 470);
        $this->assertTrue($game['pot'] == 65);
    }
}
