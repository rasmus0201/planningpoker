<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends AbstractRepository
{
    public function getUnconnectedUsers(): Collection
    {
        return User::withCount('connection')->having('connection_count', 0)->get();
    }

    public function getConnectedUsers(): Collection
    {
        return User::whereNotNull('client_id')
            ->withCount('connection')
            ->having('connection_count', '>', 0)
            ->get();
    }

    public function getAuthenticatedPlayers(int $gameId): Collection
    {
        return User::whereNotNull('client_id')->where('type', '!=', User::TYPE_GAMEMASTER)->whereHas('connection', function (Builder $query) use ($gameId) {
            $query->where('game_id', $gameId);
        })->get();
    }

    public function getByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function getByClientId(string $clientId): ?User
    {
        return User::where('client_id', $clientId)->first();
    }

    public function getByConnectionId(int $connectionId): ?User
    {
        return User::whereHas('connection', function (Builder $query) use ($connectionId) {
            $query->where('id', $connectionId);
        })->first();
    }

    public function getVoted(int $roundId): Collection
    {
        return User::whereNotNull('client_id')
            ->has('connection')
            ->whereHas('votes', function ($query) use ($roundId) {
                $query->where('round_id', $roundId);
            })
            ->where('type', User::TYPE_PLAYER)
            ->get();
    }

    public function countVoted(int $roundId): int
    {
        return User::whereNotNull('client_id')
            ->has('connection')
            ->whereHas('votes', function ($query) use ($roundId) {
                $query->where('round_id', $roundId);
            })
            ->where('type', User::TYPE_PLAYER)
            ->count();
    }

    public function countConnectedUsers(int $gameId): int
    {
        return User::whereNotNull('client_id')
            ->whereHas('connection', function (Builder $query) use ($gameId) {
                $query->where('game_id', $gameId);
            })
            ->where('type', User::TYPE_PLAYER)
            ->count();
    }

    public function setClientIdById(int $id, string $clientId): void
    {
        User::where('id', $id)->update([
            'client_id' => $clientId
        ]);
    }

    public function cleanPreviousByClientId(string $clientId, int $currentUserId): void
    {
        User::where('id', '!=', $currentUserId)
            ->where('client_id', $clientId)
            ->doesntHave('connection')
            ->update([
                'client_id' => null,
            ]);
    }
}
