<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GameRound extends Model
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'game_id',
    ];

    public function game(): HasOne
    {
        return $this->hasOne(Game::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(GameVote::class);
    }
}
