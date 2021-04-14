<?php

namespace App\Listeners;

use App\Models\Game;
use App\Models\User;
use App\RepositoryFactory;

class FinishRoundListener extends Listener
{
    public function listen()
    {
        return 'message:finishRound';
    }

    public function handle()
    {
        $clientId = $this->event->data['data']['clientId'] ?? '';

        if (!$clientId) {
            return;
        }

        $userRepository = RepositoryFactory::createUser();
        if (!$user = $userRepository->getByClientId($clientId)) {
            return;
        }

        if ($user->type !== User::TYPE_GAMEMASTER) {
            return;
        }

        $user->load(['game', 'game.latestRound']);
        $round = $user->game->latestRound ?? null;
        if (!$round) {
            return;
        }

        $gameRepository = RepositoryFactory::createGame();
        $gameRepository->setStateById($user->game->id, Game::STATE_SHOWOFF);

        $this->event->broadcast([
            'type' => 'setGame',
            'data' => [
                'state' => Game::STATE_SHOWOFF,
            ],
        ]);

        $voteRepository = RepositoryFactory::createVote();
        $votes = $voteRepository->getVotes($round->id)->sortBy('user.username')
            ->map(function ($item) {
                return [
                    'value' => $item->vote,
                    'username' => $item->user->username,
                ];
            })
            ->values();

        $this->event->broadcast([
            'type' => 'setVotes',
            'data' => [
                'votes' => $votes,
            ],
        ]);
    }
}
