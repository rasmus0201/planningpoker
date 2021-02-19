<?php

namespace App\Actions;

use App\Events\Event;

abstract class Action
{
    protected $event;

    abstract public function run();

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
