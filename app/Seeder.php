<?php

declare(strict_types=1);

namespace App;

use App\Models\Game;
use App\Models\User;

class Seeder
{
    public function run()
    {
        User::truncate();
        User::insert([

            // Web
            [
                'name' => 'Kim',
                'username' => 'kba',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Oliver',
                'username' => 'ofm',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Staffan',
                'username' => 'sse',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Jonas',
                'username' => 'jcl',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Rasmus',
                'username' => 'rso',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Lukas',
                'username' => 'lbk',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Mia',
                'username' => 'mng',
                'client_id' => null,
                'type' => User::TYPE_GAMEMASTER,
            ],

            // Mobile
            [
                'name' => 'Emma',
                'username' => 'eba',
                'client_id' => null,
                'type' => User::TYPE_GAMEMASTER,
            ],
            [
                'name' => 'Anders',
                'username' => 'aki',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Steffen',
                'username' => 'asa',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],

            // App
            [
                'name' => 'Torben',
                'username' => 'tmg',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Peter',
                'username' => 'pch',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Kasper',
                'username' => 'kfi',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],


            // Other
            [
                'name' => 'Guest player 1',
                'username' => 'guest1',
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ],
            [
                'name' => 'Spectator 1',
                'username' => 'spectator1',
                'client_id' => null,
                'type' => User::TYPE_SPECTATOR,
            ],
        ]);

        Game::truncate();

        Game::create([
            'pin' => 'guldfugl',
            'state' => Game::STATE_LOBBY,
        ])->rounds()->create();
    }
}
