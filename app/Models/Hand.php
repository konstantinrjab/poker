<?php

namespace App\Models;

use App\Exceptions\InvalidHandException;
use Illuminate\Support\Collection;

class Hand extends Collection
{
    public const CARD_LIMIT = 2;

    public function __construct($items = [])
    {
        if (count($items) > self::CARD_LIMIT) {
            throw new InvalidHandException();
        }
        parent::__construct($items);
    }

    public function add($card): void
    {
        if ($this->count() == self::CARD_LIMIT) {
            throw new InvalidHandException();
        }
        parent::add($card);
    }
}
