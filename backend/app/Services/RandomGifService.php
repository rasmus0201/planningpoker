<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RandomGifService
{
    private const API_URL = 'https://api.giphy.com/v1/gifs';
    private const API_KEY = '0UTRbFtkMxAplrohufYco5IY74U8hOes';

    public function get(): string
    {
        $response = Http::acceptJson()->get(self::API_URL . '/random', [
            'api_key' => self::API_KEY,
            'tag' => 'funny cats',
            'rating' => 'pg-13',
        ]);

        return $response->json('data.images.original.url', '');
    }
}
