<?php

namespace App\Models;

use App\Enums\GameState;
use App\Http\Resources\GameResource;
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo, Relations\HasMany, Relations\HasOne, SoftDeletes};

class Game extends Model
{
    use SoftDeletes;

    public $resource = GameResource::class;

    protected $fillable = [
        'user_id',
        'pin',
        'state',
        'title',
    ];

    protected $casts = [
        'state' => GameState::class,
    ];

    public function getRouteKeyName()
    {
        return 'pin';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GameParticipant::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function latestRound(): HasOne
    {
        return $this->hasOne(GameRound::class)->latestOfMany();
    }
}
