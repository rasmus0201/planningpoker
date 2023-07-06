<?php

namespace App\Http\Controllers;

use App\Enums\GameState;
use App\Events\{GameRoundStarted, GameStateChanged, GameVoteReveal};
use App\Http\ApiResponse;
use App\Http\Requests\{StoreGameRequest, UpdateGameRequest};
use App\Http\Resources\{GameParticipantResource, GameResource, GameVoteResource};
use App\Models\{Game, GameVote};
use App\Services\RandomFactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\UnauthorizedException;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ApiResponse::success(
            $this->user()->games()->orderByDesc('created_at')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGameRequest $request)
    {
        $pin = random_int(100_000, 1_000_000);

        $game = Game::create([
            'user_id' => Auth::id(),
            'title' => $request->title ?? '',
            'pin' => $pin,
            'state' => GameState::Lobby,
        ]);

        return ApiResponse::success($game);
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game, Request $request)
    {
        $rf = new RandomFactService();

        $data = [
            'game' => new GameResource($game),
            'fact' => $game->state === GameState::Lobby ? $rf->get() : '',
            'participants' => GameParticipantResource::collection(
                $game->participants()->with('user')->get()
            )->toArray($request),
            'votingUsers' => [],
            'votes' => [],
        ];

        // If voting, send users' votes through (with no vote value)
        if ($game->state === GameState::Voting) {
            $data['votingUsers'] = GameParticipantResource::collection($game->latestRound->participants)->toArray($request);
        }

        // If revealing, load latest round votes.
        if ($game->state === GameState::Revealing) {
            $votes = GameVote::with(['participant', 'participant.user'])
                ->where('game_round_id', $game->latestRound->id)
                ->get();

            $data['votes'] = GameVoteResource::collection($votes)->toArray($request);
        }

        return ApiResponse::success($data);
    }

    /**
     * Display the specified resource.
     */
    public function update(UpdateGameRequest $request, Game $game)
    {
        if ($this->user()->id !== $game->user_id) {
            throw new UnauthorizedException();
        }

        $stateHash = $game->state->value . $request->state;

        if ($request->state) {
            $game->state = $request->state;
        }

        $game->save();

        $callback = match ($stateHash) {
            // Start
            GameState::Lobby->value . GameState::Voting->value => function () use ($game) {
                $game->rounds()->create([
                    'user_id' => $this->user()->id,
                    'game_id' => $game->id,
                    'label' => 'Round #1',
                ]);

                // Send event to reset votes.
                broadcast(new GameRoundStarted($game));
            },

            // Continue to show-off (force reveal)
            GameState::Voting->value . GameState::Revealing->value => function () use ($game) {
                $game->load('latestRound');
                if (!$game->latestRound) {
                    return;
                }

                $votes = GameVote::with('participant')->where('game_round_id', $game->latestRound->id)->get();

                $collection = GameVoteResource::collection($votes);

                // Send reveal event to joined users.
                broadcast(new GameVoteReveal(
                    $game,
                    $collection->toArray(request())
                ));
            },

            // Continue to next round
            GameState::Revealing->value . GameState::Voting->value,
            // Force continue
            GameState::Voting->value . GameState::Voting->value => function () use ($game) {
                $game->loadCount('rounds');

                $game->rounds()->create([
                    'game_id' => $game->id,
                    'label' => 'Round #' . $game->rounds_count + 1,
                ]);

                // Send event to reset votes.
                broadcast(new GameRoundStarted($game));
            },

            default => function () {
                // Finishing and other combinations that aren't special.
            }
        };

        $callback();

        broadcast(new GameStateChanged($game));

        return ApiResponse::success($game);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        if ($this->user()->id !== $game->user_id) {
            throw new UnauthorizedException();
        }

        $game->delete();

        return ApiResponse::deleted();
    }
}
