<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class GameVote extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'round_id',
        'user_id',
        'vote',
    ];

    public function game(): HasOneThrough
    {
        return $this->hasOneThrough(
            Game::class,
            GameRound::class,
            'id',
            'id',
            'round_id',
            'game_id'
        );
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(GameRound::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
