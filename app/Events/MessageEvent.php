<?php

namespace App\Events;

use GuzzleHttp\Psr7\Request;

class MessageEvent extends Event
{
    public function __construct(Request $request, array $message)
    {
        parent::__construct('message', $request, $message);
    }
}
