<?php

namespace App\Events;

use GuzzleHttp\Psr7\Request;

class OpenEvent extends Event
{
    public function __construct(Request $request)
    {
        parent::__construct('open', $request);
    }
}
