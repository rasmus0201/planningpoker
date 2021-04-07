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
    9050
);

$server->run();
