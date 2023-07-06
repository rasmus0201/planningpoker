<?php

namespace App\Events;

use App\Enums\GameState;
use App\Models\Game;
use Illuminate\Broadcasting\{InteractsWithSockets, PresenceChannel};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStateChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public GameState $state;

    public function __construct(private Game $game)
    {
        $this->state = $this->game->state;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('games.' . $this->game->pin);
    }

    public function broadcastAs()
    {
        return 'game.state';
    }
}
