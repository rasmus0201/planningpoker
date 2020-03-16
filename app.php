<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\EventDispatcher;
use App\Socket;

require __DIR__ . '/vendor/autoload.php';

$listeners = [
    'App\Events\OpenEvent' => [],
    'App\Events\MessageEvent' => [
        'App\Listeners\ConnectListener',
        'App\Listeners\JoinListener',
        'App\Listeners\VoteListener',
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
