<?php

namespace App\Entities;

use App\Entities\Database\RedisORM;
use Illuminate\Contracts\Auth\Authenticatable;

class User extends RedisORM implements Authenticatable
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        parent::__construct();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return '';
    }

    /**
     * Get the "remember me" token value.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return '';
    }

    /**
     * Set the "remember me" token value.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {

    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return '';
    }

    protected static function getKey(): string
    {
        return 'user';
    }
}
