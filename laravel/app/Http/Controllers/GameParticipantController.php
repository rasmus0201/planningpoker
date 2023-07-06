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
        $kickedUser = GameParticipant::onlyTrashed()
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
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game, GameParticipant $gameParticipant): JsonResponse
    {
        if ($this->user()->id !== $game->user_id) {
            throw new UnauthorizedException();
        }

        $gameParticipant->delete();

        return ApiResponse::deleted();
    }
}
