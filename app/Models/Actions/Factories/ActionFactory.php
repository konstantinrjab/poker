<?php

namespace App\Models\Actions\Factories;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Actions\Abstracts\Action;
use App\Models\Actions\CheckAction;
use App\Models\Actions\FoldAction;
use App\Models\Actions\BetAction;
use App\Models\Actions\SetReadyAction;
use Exception;

class ActionFactory
{
    public const AVAILABLE_ACTIONS = [
        self::FOLD,
        self::CHECK,
        self::BET,
        self::CALL,
        self::RAISE,
        self::SET_READY,
    ];
    public const FOLD = 'fold';
    public const CHECK = 'check';
    public const BET = 'bet';
    public const CALL = 'call';
    public const RAISE = 'raise';
    public const SET_READY = 'ready';

    public static function get(UpdateGameRequest $request): Action
    {
        switch ($request->get('action')) {
            case self::FOLD:
                return new FoldAction($request);
            case self::CHECK:
                return new CheckAction($request);
            case self::BET:
                return new BetAction($request);
            case self::SET_READY:
                return new SetReadyAction($request);
        }
        throw new Exception('Unknown action type: ' . $request->get('type'));
    }
}
