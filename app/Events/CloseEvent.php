<?php

namespace App\Events;

use GuzzleHttp\Psr7\Request;

class CloseEvent extends Event
{
    public function __construct(Request $request)
    {
        parent::__construct('close', $request);
    }
}
