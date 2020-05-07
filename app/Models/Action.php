<?php

namespace App\Models;

class Action
{
    public const AVAILABLE_ACTIONS = [
        self::FOLD,
        self::CHECK,
        self::BET,
        self::CALL,
        self::RAISE,
    ];
    private const FOLD = 'fold';
    private const CHECK = 'check';
    private const BET = 'bet';
    private const CALL = 'call';
    private const RAISE = 'raise';
}
