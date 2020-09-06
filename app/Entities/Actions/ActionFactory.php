<?php

namespace App\Entities\Actions;

use App\Exceptions\GameException;
use App\Http\Requests\UpdateGameRequest;
use App\Entities\Actions\Action;
use App\Entities\Actions\BetAction;
use App\Entities\Actions\CallAction;
use App\Entities\Actions\CheckAction;
use App\Entities\Actions\FoldAction;
use App\Entities\Game;

class ActionFactory
{
    public const CHECK = 'check';
    public const BET = 'bet';

    public static function getAvailableActions(): array
    {
        return [
            FoldAction::getName(),
            CheckAction::getName(),
            CallAction::getName(),
            BetAction::getName(),
        ];
    }

    public static function get(UpdateGameRequest $request, Game $game): Action
    {
        $player = $game->getPlayers()->getById($request->input('userId'));
        $availableActions = $game->getDeal()->getRound()->getAvailableActions($player);
        $actionName = $request->input('action');
        $availableActionNames = [];
        foreach ($availableActions as $action) {
            $availableActionNames[] = $action->getName();
        }

        if (!in_array($actionName, $availableActionNames)) {
            throw new GameException(
                "Action `$actionName` is not available. Available actions are: " . implode(', ', $availableActionNames),
                403
            );
        }

        switch ($actionName) {
            case FoldAction::getName():
                return new FoldAction();
            case CheckAction::getName():
                return new CheckAction();
            case CallAction::getName():
                return new CallAction();
            case BetAction::getName():
                return new BetAction();
        }
    }
}
