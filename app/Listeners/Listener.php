<?php

namespace App\Listeners;

use App\Events\Event;

abstract class Listener
{
    protected Event $event;

    abstract public function listen();
    abstract public function handle();

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
