<?php

return [
    'name' => 'Planningpoker',
    'env' => env('APP_ENV', 'production'),
    'websocket_port' => env('WEBSOCKET_PORT', 9000),
    'debug' => (bool) env('APP_DEBUG', false),
];
