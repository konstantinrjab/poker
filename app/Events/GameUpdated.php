<?php

namespace App\Events;

use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn()
    {
        return new Channel('game.' . $this->game->getId());
    }

    public function broadcastWith()
    {
        // TODO: broadcast game with users cards to each user directly, hide card for another players
        return GameResource::make($this->game)->resolve();
    }
}
