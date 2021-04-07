<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public const TYPE_PLAYER = 'player';
    public const TYPE_GAMEMASTER = 'gamemaster';
    public const TYPE_SPECTATOR = 'spectator';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = [
        'name',
        'username',
        'client_id',
        'type',
    ];

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }
}
