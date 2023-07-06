<?php

namespace App\Http\Controllers;

use App\Http\ApiResponse;
use App\Http\Resources\GameVoteResource;
use App\Models\{Game, GameParticipant};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\UnauthorizedException;

class GameParticipantController extends Controller
{
    public function index(Game $game): JsonResponse
    {
        if (!$participant = $this->user()->participant($game)) {
            return ApiResponse::success();
        }

        $currentVote = null;
        if ($game->latestRound) {
            $currentVote = $game->latestRound->votes()->where('game_participant_id', $participant->id)->first();
        }

        return ApiResponse::success([
            'currentVote' => $currentVote ? new GameVoteResource($currentVote) : null,
            'participantId' => $participant->id,
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Game $game, Request $request): JsonResponse
    {
        $kickedUser = GameParticipant::whereNotNull('kicked_at')
            ->where('game_id', $game->id)
            ->where('user_id', $this->user()->id)
            ->exists();
        if ($kickedUser) {
            throw new UnauthorizedException();
        }

        if ($this->user()->participant($game)) {
            return ApiResponse::success();
        }

        GameParticipant::create([
            'game_id' => $game->id,
            'user_id' => $this->user()->id,
        ]);

        return ApiResponse::created();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Game $game, Request $request, int $gameParticipantId)
    {
        if ($this->user()->id !== $game->user_id) {
            throw new UnauthorizedException();
        }

        $gameParticipant = GameParticipant::where('game_id', $game->id)->where('id', $gameParticipantId)->firstOrFail();

        $request->validate([
            'isKicked' => ['required', 'boolean'],
        ]);

        $gameParticipant->update([
            'kicked_at' => $request->isKicked ? now() : null,
        ]);

        return ApiResponse::success($gameParticipant);
    }
}
