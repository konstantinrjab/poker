<?php

namespace App\Models;

use App\Http\Requests\UpdateGameRequest;

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

    private string $type;
    private ?int $value;

    public function __construct(UpdateGameRequest $request)
    {
        $this->type = $request->get('action');
        $this->value = $request->get('value');
    }

    public function updateRound(Round $round): void
    {

    }
}
