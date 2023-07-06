<?php

namespace App\Events;

use App\Http\Resources\GameParticipantResource;
use App\Models\Game;
use Illuminate\Broadcasting\{InteractsWithSockets, PresenceChannel};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameVoteCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public GameParticipantResource $participant,
        private Game $game
    ) {
    }

    public function broadcastOn()
    {
        return new PresenceChannel('games.' . $this->game->pin);
    }

    public function broadcastAs()
    {
        return 'game.vote';
    }
}
