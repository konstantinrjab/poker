<?php

namespace App\Providers;

use App\Events\GameUpdated;
use App\Models\Game;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if (env('SOCKETS_ENABLED', true)) {
            Event::listen(GameUpdated::NAME, function (Game $game) {
                foreach ($game->getPlayers() as $player) {
                    GameUpdated::dispatch($game, $player->getId());
                }
            });
        }
    }
}
