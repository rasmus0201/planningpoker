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
class OldLog extends Logger
{
    private static $instance = null;

    private Logger $logger;

    private function __construct()
    {
        $this->logger = new Logger('PlanningPokerLogger');
        $this->logger->pushHandler(new StreamHandler(ABS_PATH.'/storage/planningpoker.log'));
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Log();
        }

        return self::$instance;
    }

    // public static function __callStatic($name, $arguments)
    // {
    //     $instance = self::getInstance();

    //     $instance->getLogger()->log($name, ...$arguments);
    // }

    public function getLogger()
    {
        return $this->logger;
    }
}
