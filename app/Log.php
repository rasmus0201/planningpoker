<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log extends Logger
{
    public function __construct(Application $app)
    {
        parent::__construct('PlanningPokerLogger', [
            new StreamHandler($app->basePath('storage').'/planningpoker.log')
        ]);
    }
}
