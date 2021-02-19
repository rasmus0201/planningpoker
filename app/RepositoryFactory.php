<?php

namespace App;

use App\Repositories\AbstractRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoteRepository;

class RepositoryFactory
{
    private static $repositories = [];

    public static function createUser(): UserRepository
    {
        return static::getRepository('user', UserRepository::class);
    }

    public static function createVote(): VoteRepository
    {
        return static::getRepository('vote', VoteRepository::class);
    }

    private static function getRepository($key, $class): AbstractRepository
    {
        if (!isset(static::$repositories[$key])) {
            static::$repositories[$key] = new $class();
        }

        return static::$repositories[$key];
    }
}
