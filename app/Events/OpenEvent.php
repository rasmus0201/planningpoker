<?php

namespace App\Events;

class OpenEvent extends Event
{
    public function __construct()
    {
        parent::__construct('open');
    }
}
