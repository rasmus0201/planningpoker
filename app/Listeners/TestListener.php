<?php

namespace App\Listeners;

use App\Application;
use App\Events\Event;

class TestListener extends Listener
{
    private Application $app;

    public function __construct(
        Application $app,
        Event $event
    ) {
        parent::__construct($event);

        $this->app = $app;
    }

    public function listen()
    {
        return 'message:test';
    }

    public function handle()
    {
        $this->app->make('log')->debug('test');
        $this->event->sendPublisher([
            'what' => 'hello'
        ]);
    }
}
