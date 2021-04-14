<?php

namespace App\Listeners;

use App\Models\Game;
use App\Models\User;
use App\RepositoryFactory;

class StartGameListener extends Listener
{
    public function listen()
    {
        return 'message:startGame';
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

        $gameRepository = RepositoryFactory::createGame();
        $gameRepository->setStateById($user->game->id, Game::STATE_PLAYING);

        // Reset votes to make sure no previous data was stored
        $this->event->broadcast([
            'type' => 'setVotes',
            'data' => [
                'votes' => [],
            ],
        ]);

        $this->event->broadcast([
            'type' => 'setGame',
            'data' => [
                'state' => Game::STATE_PLAYING,
            ],
        ]);
    }
}
