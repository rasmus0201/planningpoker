<?php

use App\Application;
use App\EventDispatcher;
use App\Log;
use Dotenv\Dotenv;
use Monolog\ErrorHandler;

define('ABS_PATH', dirname(__DIR__));
error_reporting(E_ALL & ~E_WARNING);

require ABS_PATH . '/vendor/autoload.php';
require ABS_PATH . '/bootstrap/functions.php';

// Set the logger as the error handler as well
$logger = new Log(ABS_PATH . '/storage');
ErrorHandler::register($logger);

// Load .env
$dotenv = Dotenv::createImmutable(ABS_PATH);
$dotenv->load();

// Bootstrap application
$app = new Application(ABS_PATH);

$app->configure('app');
$app->configure('database');
$app->configure('events');

$app->boot();

// Register the logging class
$app->singleton(Log::class, function () use ($logger) {
    return $logger;
});
$app->alias(Log::class, 'log');

$app->singleton(EventDispatcher::class, function (Application $app) {
    return new EventDispatcher($app, config('events'));
});

$app->singleton(\Illuminate\Filesystem\FilesystemManager::class, function (Application $app) {
    return new \Illuminate\Filesystem\FilesystemManager($app);
});
