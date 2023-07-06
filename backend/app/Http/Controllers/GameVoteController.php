<?php

namespace App\Http\Controllers;

use App\Enums\GameState;
use App\Events\{GameStateChanged, GameVoteCreated, GameVoteReveal};
use App\Http\ApiResponse;
use App\Http\Resources\{GameParticipantResource, GameVoteResource};
use App\Models\{Game, GameVote};
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GameVoteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Game $game, Request $request)
    {
        if (!$game->latestRound) {
            throw new BadRequestHttpException();
        }

        if (!$participant = $this->user()->participant($game)) {
            throw new BadRequestHttpException();
        }

        if ($participant->kicked_at) {
            throw new BadRequestHttpException();
        }

        $hasExistingVote = GameVote::where('game_round_id', $game->latestRound->id)
            ->where('game_participant_id', $participant->id)
            ->exists();
        if ($hasExistingVote) {
            throw new BadRequestHttpException();
        }

        $request->validate([
            'vote' => ['required', 'string', 'max:12'],
        ]);

        $game->load(['latestRound', 'latestRound.votes']);

        $game->latestRound->votes()->create([
            'game_participant_id' => $participant->id,
            'vote' => $request->vote,
        ]);

        $participant->load('user');

        // Broadcast that the user voted.
        broadcast(new GameVoteCreated(new GameParticipantResource($participant), $game));

        // Check if all participants voted - if so broadcast reveal event and update game state.
        $votesDiff = $game->participants()->whereNull('kicked_at')
            ->pluck('id')
            ->diff($game->latestRound->votes()->pluck('game_participant_id'));

        if ($votesDiff->isEmpty()) {
            $game->state = GameState::Revealing;
            $game->save();

            $votes = GameVote::with(['participant', 'participant.user'])->where('game_round_id', $game->latestRound->id)->get();

            broadcast(new GameStateChanged($game));
            broadcast(new GameVoteReveal(
                $game,
                GameVoteResource::collection($votes)->toArray($request)
            ));
        }

        return ApiResponse::success();
    }
}
