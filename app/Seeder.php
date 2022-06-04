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

        $users = [];

        foreach (config('app.players') as $player) {
            $users[] = [
                'name' => $player,
                'username' => $player,
                'client_id' => null,
                'type' => User::TYPE_PLAYER,
            ];
        }

        foreach (config('app.spectators') as $spectator) {
            $users[] = [
                'name' => $spectator,
                'username' => $spectator,
                'client_id' => null,
                'type' => User::TYPE_SPECTATOR,
            ];
        }

        foreach (config('app.game_masters') as $gameMaster) {
            $users[] = [
                'name' => $gameMaster,
                'username' => $gameMaster,
                'client_id' => null,
                'type' => User::TYPE_GAMEMASTER,
            ];
        }

        User::insert($users);

        Game::truncate();

        Game::create([
            'pin' => 'guldfugl',
            'state' => Game::STATE_LOBBY,
        ])->rounds()->create();
    }
}
