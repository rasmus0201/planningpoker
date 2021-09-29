<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log extends Logger
{
    private static ?Log $instance;

    public function __construct($storagePath)
    {
        self::$instance = null;

        parent::__construct('PlanningPokerLogger', [
            new StreamHandler($storagePath . '/planningpoker.log')
        ]);
    }

    public static function get(): Log
    {
        if (self::$instance === null) {
            self::$instance = app()->make(Log::class);
        }

        return self::$instance;
    }
}
