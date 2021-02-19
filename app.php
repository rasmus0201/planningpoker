<?php

use App\EventDispatcher;
use App\Log;
use App\Socket;
use Monolog\ErrorHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

define('ABS_PATH', __DIR__);

error_reporting(E_ALL & ~E_WARNING);

require __DIR__ . '/vendor/autoload.php';

// Reigster error logging
$log = Log::getInstance();
ErrorHandler::register($log->getLogger());

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
