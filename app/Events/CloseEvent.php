<?php

namespace App\Events;

class CloseEvent extends Event
{
    public function __construct()
    {
        parent::__construct('close');
    }
}
