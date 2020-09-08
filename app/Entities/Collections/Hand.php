<?php

namespace App\Entities\Collections;

use Illuminate\Support\Collection;
use Exception;

class Hand extends Collection
{
    public const CARDS_COUNT = 2;

    public function __construct($items = [])
    {
        if (count($items) != self::CARDS_COUNT) {
            throw new Exception('Hand must have ' . self::CARDS_COUNT . ' cards');
        }
        parent::__construct($items);
    }

    public function add($card): void
    {
        throw new Exception('Cannot add card to Hand');
    }
}
