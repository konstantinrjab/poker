<?php

namespace App\Http\Resources;

use App\Http\Resources\Collections\PlayerResourceCollection;
use App\Entities\Actions\BetAction;
use Facades\App\Http\Adapters\CardAdapter;
use App\Entities\Game;
use App\Entities\Player;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PlayerResource
 * @package App\Http\Resources
 *
 * @mixin Player
 */
class PlayerResource extends JsonResource
{
    private string $userId;

    public function setUserId(string $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public static function idCollection($resource, $userId): PlayerResourceCollection
    {
        return tap(new PlayerResourceCollection($resource, static::class, $userId), function ($collection) use ($userId) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $game = $this->getGame();

        return [
            'id' => $this->when($this->getId() == $this->userId, $this->getId()),
            'name' => $this->getName(),
            'money' => $this->getMoney(),
            'bet' => $game->getDeal() ? $game->getDeal()->getRound()->getPlayerBet($this->getId()) : null,
            'isReadyToStart' => $this->getIsReady(),
            'isFolded' => $this->getIsFolded(),
            'isCreator' => $this->isCreator(),
            'isDealer' => $this->isDealer(),
            'isBigBlind' => $this->isBigBlind(),
            'isSmallBlind' => $this->isSmallBlind(),
            'isActive' => $this->isActive(),
            'holeCards' => $this->getId() == $this->userId && $this->getHand() ? CardAdapter::handle($this->getHand()) : [],
            'availableActions' => $this->getAvailableActions(),
        ];
    }

    private function isCreator(): bool
    {
        return $this->getId() == $this->getGame()->getCreatorId();
    }

    private function isDealer(): bool
    {
        $dealer = $this->getGame()->getPlayers()->getDealer();
        return $dealer && $this->getId() == $dealer->getId();
    }

    private function isSmallBlind(): bool
    {
        $smallBlind = $this->getGame()->getPlayers()->getSmallBlind();
        return $smallBlind && $this->getId() == $smallBlind->getId();
    }

    private function isBigBlind(): bool
    {
        $bigBlind = $this->getGame()->getPlayers()->getBigBlind();
        return $bigBlind && $this->getId() == $bigBlind->getId();
    }

    private function isActive(): bool
    {
        $activePlayer = $this->getGame()->getPlayers()->getActivePlayer();

        return $activePlayer && $this->getId() == $activePlayer->getId();
    }

    private function getAvailableActions(): ?array
    {
        $game = $this->getGame();
        $actions = $game->getDeal() && $game->getDeal()->getRound() ? $game->getDeal()->getRound()->getAvailableActions($this->resource) : null;
        if (!$actions) {
            return null;
        }

        $result = [];
        foreach ($actions as $action) {
            $arrayActon = [];
            $arrayActon['type'] = $action::getName();
            if ($action instanceof BetAction) {
                $minRaise = BetAction::getMinBet($game);
                $arrayActon += [
                    'min' => $minRaise,
                    'max' => $this->resource->getMoney()
                ];
            }
            $result[] = $arrayActon;
        }

        return $result;
    }

    private function getGame(): Game
    {
        return app()->get('game.instance');
    }
}
