<?php

return [
    'name' => 'Planningpoker',
    'env' => env('APP_ENV', 'production'),
    'websocket_port' => env('WEBSOCKET_PORT', 9000),
    'debug' => (bool) env('APP_DEBUG', false),
    'players' => explode(',', env('GAME_PLAYERS', '')),
    'spectators' => explode(',', env('GAME_SPECTATORS', '')),
    'game_masters' => explode(',',  env('GAME_GAME_MASTERS', '')),
];
