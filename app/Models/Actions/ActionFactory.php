<?php

namespace App\Models\Actions;

use App\Http\Requests\UpdateGameRequest;
use App\Models\Actions\Abstracts\Action;
use Exception;

class ActionFactory
{
    public const AVAILABLE_ACTIONS = [
        self::FOLD,
        self::CHECK,
        self::BET,
        self::CALL,
        self::RAISE,
    ];
    public const FOLD = 'fold';
    public const CHECK = 'check';
    public const BET = 'bet';
    public const CALL = 'call';
    public const RAISE = 'raise';

    public static function get(UpdateGameRequest $request): Action
    {
        switch ($request->get('type')) {
            case self::RAISE:
                return new RaiseAction($request);
            // TODO: finish this
            case self::FOLD:
                return new FoldAction($request);
        }
        throw new Exception('Unknown action type: ' . $request->get('type'));
    }
}
