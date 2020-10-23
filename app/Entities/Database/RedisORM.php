<?php

namespace App\Entities\Database;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;

abstract class RedisORM
{
    abstract protected static function getKey(): string;

    public static function get(string $id, bool $throwOnNotFound = true): ?self
    {
        $entity = Redis::get(static::getKey() . ':' . $id);
        if (!$entity && $throwOnNotFound) {
            throw new ModelNotFoundException();
        }
        return $entity ? unserialize($entity) : null;
    }

    public function save()
    {
        Redis::set(static::getKey() . ':' . $this->getId(), serialize($this), 'EX', 3600);
    }
}
