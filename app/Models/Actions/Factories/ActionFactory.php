<?php

namespace App\Models\Actions\Factories;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Actions\Abstracts\Action;
use App\Models\Actions\CheckAction;
use App\Models\Actions\FoldAction;
use App\Models\Actions\CallAction;
use App\Models\Actions\BetAction;
use Exception;

class ActionFactory
{
    public const AVAILABLE_ACTIONS = [
        self::FOLD,
        self::CHECK,
        self::CALL,
        self::BET,
    ];
    public const FOLD = 'fold';
    public const CHECK = 'check';
    public const CALL = 'call';
    public const BET = 'bet';

    public static function get(UpdateGameRequest $request): Action
    {
        switch ($request->input('action')) {
            case self::FOLD:
                return new FoldAction($request);
            case self::CHECK:
                return new CheckAction($request);
            case self::CALL:
                return new CallAction($request);
            case self::BET:
                return new BetAction($request);
        }
        throw new Exception('Unknown action type: ' . $request->input('type'));
    }
}
