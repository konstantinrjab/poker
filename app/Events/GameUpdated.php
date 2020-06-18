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

    public const NAME = 'game.updated';

    private Game $game;
    private string $userId;

    public function __construct(Game $game, string $userId)
    {
        $this->game = $game;
        $this->userId = $userId;
        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn()
    {
        // TODO: make channel private
        return new Channel('game.' . $this->game->getId() . '.' . $this->userId);
    }

    public function broadcastAs()
    {
        return self::NAME;
    }

    public function broadcastWith()
    {
        return GameResource::make($this->game)->resolve();
    }
}
