<?php

return [
    \App\Events\OpenEvent::class => [
        \App\Listeners\ConnectListener::class,
        \App\Listeners\JoinListener::class,
    ],
    \App\Events\MessageEvent::class => [
        \App\Listeners\LoginListener::class,
        \App\Listeners\StartGameListener::class,
        \App\Listeners\FinishGameListener::class,
        \App\Listeners\FinishRoundListener::class,
        \App\Listeners\AdvanceRoundListener::class,
        \App\Listeners\VoteListener::class,
    ],
    \App\Events\CloseEvent::class => [],
    \App\Events\ErrorEvent::class => [],
];
