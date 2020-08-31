<?php

namespace App\Entities;

use App\Entities\Database\RedisORM;
use Illuminate\Support\Str;

class User extends RedisORM
{
    private string $name;
    private string $id;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->id = Str::uuid();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected static function getKey(): string
    {
        return 'user';
    }
}
