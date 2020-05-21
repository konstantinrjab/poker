<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    const CREATOR_ID = 'testUserId1';

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }

    public function testFlow()
    {
        $gameId = $this->create();
        $this->join($gameId);
        $this->start($gameId);
        $this->update($gameId);
        $response = $this->get('/api/games/' . $gameId)->json();
    }

    private function create(): string
    {
        $response = $this->post('/api/games', [
            'userId' => self::CREATOR_ID,
            'name' => 'creator'
        ]);
        $gameId = $response->json()['gameId'];
        $this->assertIsString($gameId);

        return $gameId;
    }

    private function join(string $gameId)
    {
        for ($userNumber = 2; $userNumber <= 5; $userNumber++) {
            $response = $this->put('/api/games/' . $gameId . '/join', [
                'userId' => 'testUserrId' . $userNumber,
                'name' => 'player_' . $userNumber
            ]);
            $this->assertTrue($response->status() == 200);

            if ($userNumber == 3) {
                $response = $this->put('/api/games/' . $gameId . '/start', [
                    'userId' => self::CREATOR_ID
                ]);
                $this->assertTrue($response->status() == 400);
            }
        }
    }

    private function start(string $gameId)
    {
        $response = $this->put('/api/games/' . $gameId . '/start', [
            'userId' => 'incorrectUserId'
        ]);
        $this->get('/api/games/' . $gameId);
        $this->assertTrue($response->status() == 403);
        $response = $this->put('/api/games/' . $gameId . '/start', [
            'userId' => self::CREATOR_ID
        ]);
        $this->assertTrue($response->status() == 200);
    }

    private function update(string $gameId)
    {
        $response = $this->put('/api/games/' . $gameId, [
            'userId' => 'testUserId2',
            'action' => 'fold'
        ]);
        $this->assertTrue($response->status() == 400);

        $response = $this->put('/api/games/' . $gameId, [
            'userId' => self::CREATOR_ID,
            'action' => 'fold'
        ]);
        $this->assertTrue($response->status() == 200);
    }
}
