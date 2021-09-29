<?php

namespace App\Repositories;

use App\Models\Game;
use App\Models\GameRound;

class GameRepository extends AbstractRepository
{
    public function getByPin(string $pin): ?Game
    {
        return Game::where('pin', $pin)->first();
    }

    public function setStateById(int $id, string $state): void
    {
        Game::where('id', $id)->update([
            'state' => $state
        ]);
    }

    public function createNewRound(int $id): void
    {
        GameRound::create([
            'game_id' => $id,
        ]);
    }
}
