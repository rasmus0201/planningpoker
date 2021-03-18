<?php

use App\Application;
use App\EventDispatcher;
use App\Log;
use App\Socket;
use Dotenv\Dotenv;
use Monolog\ErrorHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

define('ABS_PATH', __DIR__);

error_reporting(E_ALL & ~E_WARNING);

require __DIR__ . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv::createImmutable(ABS_PATH);
$dotenv->load();

$app = new Application(ABS_PATH);

$app->configure('app');
$app->configure('database');

// Load eloquent
$app->make('db');

// Register the logging class
$app->singleton(Log::class, function (Application $app) {
    $logger = new Log($app);

    // Set the logger as the error handler as well
    ErrorHandler::register($logger);

    return $logger;
});
$app->alias(Log::class, 'log');

$app->singleton(EventDispatcher::class, function(Application $app) {
    $listeners = [
        \App\Events\OpenEvent::class => [],
        \App\Events\MessageEvent::class => [
            \App\Listeners\TestListener::class,
            \App\Listeners\ConnectListener::class,
            \App\Listeners\JoinListener::class,
            \App\Listeners\VoteListener::class,
            \App\Listeners\AdvanceListener::class,
        ],
        \App\Events\CloseEvent::class => [
            \App\Listeners\CloseListener::class,
        ],
        \App\Events\ErrorEvent::class => [],
    ];

    return new EventDispatcher($app, $listeners);
});

// Setup database connection
// https://www.amitmerchant.com/how-to-utilize-capsule-use-eloquent-orm-outside-laravel/
// use Illuminate\Database\Capsule\Manager as Capsule;
// $capsule = new Capsule;
// $capsule->addConnection([
//     'driver'    => 'mysql',
//     'host'      => env('DB_HOST'),
//     'database'  => env('DB_DATABASE'),
//     'username'  => env('DB_USERNAME'),
//     'password'  => env('DB_PASSWORD'),
//     'charset'   => 'utf8',
//     'collation' => 'utf8_unicode_ci',
//     'prefix'    => '',
// ]);
// $capsule->setAsGlobal();
// $app->singleton('db', function() use ($capsule) {
//     return $capsule;
// });

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Socket($app)
        )
    ),
    9050
);

$server->run();
