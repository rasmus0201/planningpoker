<?php

namespace App;

use App\Repositories\AbstractRepository;
use App\Repositories\ConnectionRepository;
use App\Repositories\GameRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoteRepository;

class RepositoryFactory
{
    private static $repositories = [];

    public static function createConnection(): ConnectionRepository
    {
        return static::getRepository('connection', ConnectionRepository::class);
    }

    public static function createUser(): UserRepository
    {
        return static::getRepository('user', UserRepository::class);
    }

    public static function createGame(): GameRepository
    {
        return static::getRepository('game', GameRepository::class);
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
