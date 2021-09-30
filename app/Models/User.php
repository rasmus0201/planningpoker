<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User extends Model
{
    public const TYPE_PLAYER = 'player';
    public const TYPE_GAMEMASTER = 'gamemaster';
    public const TYPE_SPECTATOR = 'spectator';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'connection_id',
        'client_id',
        'name',
        'username',
        'type',
    ];

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public function connection(): HasOne
    {
        return $this->hasOne(Connection::class);
    }

    public function game(): HasOneThrough
    {
        return $this->hasOneThrough(
            Game::class,
            Connection::class,
            'id',
            'id',
            'connection_id',
            'game_id'
        );
    }

    public function votes(): HasMany
    {
        return $this->hasMany(GameVote::class);
    }
}
