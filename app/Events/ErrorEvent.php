<?php

namespace App\Events;

class ErrorEvent extends Event
{
    public function __construct($error)
    {
        parent::__construct('error', $error);
    }
}
