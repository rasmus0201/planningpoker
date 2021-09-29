<?php

namespace App\Repositories;

use App\Models\GameVote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoteRepository extends AbstractRepository
{
    public function getVotes(int $roundId): Collection
    {
        return GameVote::with([
            'user' => function (BelongsTo $query) {
                $query->whereNotNull('client_id');
            }
        ])->where('round_id', $roundId)->get();
    }

    public function getUserVote(int $roundId, int $userId): ?GameVote
    {
        return GameVote::where('round_id', $roundId)->where('user_id', $userId)->first();
    }

    public function insertVote(int $roundId, int $userId, string $vote): void
    {
        GameVote::create([
            'round_id' => $roundId,
            'user_id' => $userId,
            'vote' => $vote,
        ]);
    }
}
