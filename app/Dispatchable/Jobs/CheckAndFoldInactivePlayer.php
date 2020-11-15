<?php

namespace App\Dispatchable\Jobs;

use App\Entities\Game\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAndFoldInactivePlayer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Game $game;
    private string $activePlayerId;
    private string $roundId;

    public function __construct(Game $game)
    {
        $this->game = $game;
        $this->roundId = $game->getDeal()->getRound()->getId();
        $this->activePlayerId = $game->getPlayers()->getActivePlayer()->getId();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // TODO: kill game if there are no turns from user for last 10 mins
        $game = Game::get($this->game->getId());
        $currentRoundId = $game->getDeal()->getRound()->getId();
        $currentActivePlayer = $game->getPlayers()->getActivePlayer();
        if (
            $game->getStatus() != Game::STATUS_FINISHED
            && $currentRoundId == $this->roundId
            && $currentActivePlayer->getId() == $this->activePlayerId
        ) {
            $currentActivePlayer->setIsFolded(true);
            $game->onAfterUpdate();
            $game->checkForNewDeal();
        }
    }
}
