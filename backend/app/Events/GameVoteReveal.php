<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\{InteractsWithSockets, PresenceChannel};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameVoteReveal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $votes;

    public function __construct(private Game $game, array $votes)
    {
        $this->votes = $votes;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('games.' . $this->game->pin);
    }

    public function broadcastAs()
    {
        return 'game.reveal';
    }
}
