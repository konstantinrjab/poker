<?php

namespace App\Entities\Database;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use Str;

abstract class RedisORM
{
    public string $id;

    abstract protected static function getKey(): string;

    public function __construct()
    {
        $this->id = Str::uuid();
    }

    public static function get(string $id, bool $throwOnNotFound = true): ?self
    {
        $entity = Redis::get(static::getKey() . ':' . $id);
        if (!$entity && $throwOnNotFound) {
            throw new ModelNotFoundException();
        }
        return $entity ? unserialize($entity) : null;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function save()
    {
        Redis::set(static::getKey() . ':' . $this->getId(), serialize($this), 'EX', 3600);
        $this->afterSave();
    }

    protected function afterSave(): void
    {

    }
}
