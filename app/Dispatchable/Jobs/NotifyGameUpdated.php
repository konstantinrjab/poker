<?php

namespace App\Dispatchable\Jobs;

use App\Dispatchable\Events\GameUpdated;
use App\Entities\Game\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Auth;

class NotifyGameUpdated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Game $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->game->getPlayers() as $player) {
            if ($player->getId() != Auth::id()) {
                GameUpdated::dispatch($this->game, $player->getId());
            }
        }
    }
}
