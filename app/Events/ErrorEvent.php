<?php

namespace App\Events;

use GuzzleHttp\Psr7\Request;

class ErrorEvent extends Event
{
    public function __construct(Request $request, array $error)
    {
        parent::__construct('error', $request, $error);
    }
}
