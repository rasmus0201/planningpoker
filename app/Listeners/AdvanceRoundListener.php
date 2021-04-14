<?php

namespace App\Listeners;

use App\Models\Game;
use App\Models\User;
use App\RepositoryFactory;

class AdvanceRoundListener extends Listener
{
    public function listen()
    {
        return 'message:advanceRound';
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

        $user->load('game');
        if (!$user->game) {
            return;
        }

        $gameRepository = RepositoryFactory::createGame();
        $gameRepository->createNewRound($user->game->id);
        $gameRepository->setStateById($user->game->id, Game::STATE_PLAYING);

        $this->event->broadcast([
            'type' => 'setGame',
            'data' => [
                'state' => Game::STATE_PLAYING,
            ],
        ]);

        $this->event->broadcast([
            'type' => 'setVotingUsers',
            'data' => [
                'users' => [],
            ],
        ]);

        $this->event->broadcast([
            'type' => 'setVotes',
            'data' => [
                'votes' => [],
            ],
        ]);
    }
}
