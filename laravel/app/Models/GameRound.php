<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\BelongsToMany, Relations\HasMany, SoftDeletes};

class GameRound extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'game_id',
        'label',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(GameVote::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            GameParticipant::class,
            'game_votes'
        )->with('user');
    }
}
