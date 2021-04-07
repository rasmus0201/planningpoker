<?php

declare(strict_types=1);

namespace App;

use App\Models\User;

class Seeder
{
    public function run()
    {
        User::truncate();
        User::insert([
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
            [
                'name' => 'Gæst',
                'username' => 'guest',
                'client_id' => null,
                'type' => User::TYPE_SPECTATOR,
            ],
        ]);
    }
}
