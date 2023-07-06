<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class GameVote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'game_round_id',
        'game_participant_id',
        'vote',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(GameParticipant::class, 'game_participant_id');
    }
}
