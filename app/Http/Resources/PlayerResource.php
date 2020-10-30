<?php

namespace App\Http\Resources;

use App\Entities\Actions\CallAction;
use App\Entities\Game\Deal;
use App\Http\Resources\Collections\PlayerResourceCollection;
use App\Entities\Actions\BetAction;
use Facades\App\Http\Adapters\CardAdapter;
use App\Entities\Game\Game;
use App\Entities\Game\Player;
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
    private Game $game;

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function setGame(Game $game): self
    {
        $this->game = $game;
        return $this;
    }

    public static function idCollection($resource, string $userId, Game $game): PlayerResourceCollection
    {
        return tap(new PlayerResourceCollection($resource, static::class, $userId, $game), function ($collection) use ($userId) {
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
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'money' => $this->getMoney(),
            'bet' => $this->game->getDeal() ? $this->game->getDeal()->getRound()->getPlayerBet($this->getId()) : null,
            'isReadyToStart' => $this->getIsReady(),
            'isFolded' => $this->getIsFolded(),
            'isCreator' => $this->isCreator(),
            'isDealer' => $this->isDealer(),
            'isBigBlind' => $this->isBigBlind(),
            'isSmallBlind' => $this->isSmallBlind(),
            'isActive' => $this->isActive(),
            'holeCards' => $this->getId() == $this->userId && $this->getHand() ? CardAdapter::handle($this->getHand()) : [],
            'availableActions' => $this->getAvailableActions(),
            'winningCombination' => $this->when($this->getStrengthDescription(), $this->getStrengthDescription())
        ];
    }

    private function isCreator(): bool
    {
        return $this->getId() == $this->game->getCreatorId();
    }

    private function isDealer(): bool
    {
        $dealer = $this->game->getPlayers()->getDealer();
        return $dealer && $this->getId() == $dealer->getId();
    }

    private function isSmallBlind(): bool
    {
        $smallBlind = $this->game->getPlayers()->getSmallBlind();
        return $smallBlind && $this->getId() == $smallBlind->getId();
    }

    private function isBigBlind(): bool
    {
        $bigBlind = $this->game->getPlayers()->getBigBlind();
        return $bigBlind && $this->getId() == $bigBlind->getId();
    }

    private function isActive(): bool
    {
        $activePlayer = $this->game->getPlayers()->getActivePlayer();

        return $activePlayer && $this->getId() == $activePlayer->getId();
    }

    private function getAvailableActions(): ?array
    {
        $deal = $this->game->getDeal();
        if (!$deal || $deal->getStatus() == Deal::STATUS_END) {
            return [];
        }

        $actions = $deal->getRound()->getAvailableActions($this->resource);
        $result = [];
        foreach ($actions as $action) {
            $arrayActon = [];
            $arrayActon['type'] = $action::getName();
            if ($action instanceof BetAction) {
                $minRaise = BetAction::getMinBet($this->game);
                $arrayActon += [
                    'min' => $minRaise,
                    'max' => $this->resource->getMoney()
                ];
            }
            if ($action instanceof CallAction) {
                $amountToCall = CallAction::getAmountToCall($this->game->getDeal()->getRound(), $this->getId());
                $arrayActon += [
                    'value' => $amountToCall,
                ];
            }
            $result[] = $arrayActon;
        }

        return $result;
    }
}
