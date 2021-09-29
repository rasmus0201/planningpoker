<?php

namespace App;

use GuzzleHttp\Psr7\Request;

class Session
{
    private static array $default = [
        'clientId' => '',
        'gamepin' => '',
    ];

    private array $session;

    public function __construct(string $clientId, string $gamePin)
    {
        $this->session = [
            'clientId' => $clientId,
            'gamepin' => $gamePin,
        ];
    }

    public static function fromRequest(Request $request)
    {
        parse_str($request->getUri()->getQuery(), $query);

        if (!isset($query['session'])) {
            return new self(static::$default['clientId'], static::$default['gamepin']);
        }

        $s = $query['session'];
        if (!isset($s['clientId'], $s['gamepin'])) {
            return new self(static::$default['clientId'], static::$default['gamepin']);
        }

        return new self($s['clientId'], $s['gamepin']);
    }

    /**
     * @param string $key
     * @param string[]|string $default
     *
     * @return string[]|string
     */
    public function get($key = null, $default = null)
    {
        if (!$key) {
            return $this->session;
        }

        return $this->session[$key] ?? $default;
    }
}
