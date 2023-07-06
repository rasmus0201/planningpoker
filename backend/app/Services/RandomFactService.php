<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RandomFactService
{
    private const API_URL = 'https://api.api-ninjas.com/v1/facts';
    private const ORIGIN = 'api.api-ninjas.com';

    public function get($limit = 1): string
    {
        $response = Http::withHeaders([
            'Origin' => self::ORIGIN,
        ])->acceptJson()->get(self::API_URL, [
            'limit' => $limit,
        ]);

        return $response->json('0.fact', 'No random fact today.');
    }
}
