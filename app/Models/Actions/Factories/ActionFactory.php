<?php

namespace App\Models\Actions\Factories;

use App\Exceptions\GameException;
use App\Http\Requests\UpdateGameRequest;
use App\Models\Actions\Abstracts\Action;
use App\Models\Actions\BetAction;
use App\Models\Actions\CallAction;
use App\Models\Actions\CheckAction;
use App\Models\Actions\FoldAction;
use App\Models\Game;

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
                "Action $actionName is not available. Available actions are: " . implode(', ', $availableActionNames),
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
