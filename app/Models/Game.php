<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Game extends Model
{
    public const STATE_LOBBY = 'LOBBY';
    public const STATE_PLAYING = 'PLAYING';
    public const STATE_SHOWOFF = 'SHOWOFF';
    public const STATE_FINISHED = 'FINISHED';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pin',
        'state',
        'created_at',
    ];

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function latestRound(): HasOne
    {
        return $this->hasOne(GameRound::class)->orderBy('id', 'desc');
    }
}
