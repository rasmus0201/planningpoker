<?php

use App\EventDispatcher;
use App\Socket;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '/vendor/autoload.php';

// Reigster error logging
$logger = new Logger('PlanningPokerLogger');
$logger->pushHandler(new StreamHandler(__DIR__.'/storage/planningpoker.log'));
ErrorHandler::register($logger);

$listeners = [
    'App\Events\OpenEvent' => [],
    'App\Events\MessageEvent' => [
        'App\Listeners\ConnectListener',
        'App\Listeners\JoinListener',
        'App\Listeners\VoteListener',
        'App\Listeners\AdvanceListener',
    ],
    'App\Events\CloseEvent' => [
        'App\Listeners\CloseListener',
    ],
    'App\Events\ErrorEvent' => [],
];

$eventDispatcher = new EventDispatcher($listeners);

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Socket($eventDispatcher)
        )
    ),
    9000
);

$server->run();
