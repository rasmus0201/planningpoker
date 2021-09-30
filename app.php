<?php

use App\Socket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require_once 'bootstrap/index.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Socket($app)
        )
    ),
    config('app.websocket_port')
);

$server->run();
