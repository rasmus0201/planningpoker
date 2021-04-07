<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @method static void debug($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void emergency($message, array $context = [])
 */
class Log extends Logger
{
    public function __construct($storagePath)
    {
        parent::__construct('PlanningPokerLogger', [
            new StreamHandler($storagePath . '/planningpoker.log')
        ]);
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = app()->make(Log::class);

        $instance->log($name, ...$arguments);
    }
}
