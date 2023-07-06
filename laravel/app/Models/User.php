<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasManyThrough};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $resource = UserResource::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'username',
        'password',
        'last_active_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_active_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getAuthIdentifierForBroadcasting(): string
    {
        if (request('join_type')) {
            return (string) $this->id .  '.' . request('join_type');
        }

        return (string) $this->id;
    }

    public function sessions(): HasMany
    {
        return $this->hasMany('sessions');
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function gameVotes(): HasManyThrough
    {
        return $this->hasManyThrough(GameVote::class, GameParticipant::class);
    }

    public function participant(Game $game): ?GameParticipant
    {
        return GameParticipant::where('game_id', $game->id)->where('user_id', $this->id)->first();
    }
}
