<?php

namespace App\Events;

class MessageEvent extends Event
{
    public function __construct($message)
    {
        parent::__construct('message', $message);
    }
}
